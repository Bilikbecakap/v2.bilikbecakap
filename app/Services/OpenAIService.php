<?php

namespace App\Services;

use App\Contracts\AIServiceInterface;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class OpenAIService extends AIBaseService implements AIServiceInterface
{
    private string $apiKey;
    private string $model;
    private string $apiUrl = 'https://api.openai.com/v1/chat/completions';
    private Client $client;

    public function __construct()
    {
        $this->apiKey = config('ai.openai.api_key');
        $this->model  = config('ai.openai.model', 'gpt-4o-mini');
        $this->client = new Client();
    }

    // -------------------------------------------------------------------------
    // HTTP layer
    // -------------------------------------------------------------------------

    private function makeRequest(array $messages, int $timeout = 30, int $maxTokens = 2048): object
    {
        try {
            $response = $this->client->post($this->apiUrl, [
                'json' => [
                    'model'      => $this->model,
                    'messages'   => $messages,
                    'max_tokens' => $maxTokens,
                ],
                'timeout' => $timeout,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'verify' => false,
            ]);

            return new class($response) {
                public function __construct(private $r) {}
                public function successful(): bool  { return $this->r->getStatusCode() === 200; }
                public function json(): array       { return json_decode((string) $this->r->getBody(), true); }
                public function body(): string      { return (string) $this->r->getBody(); }
            };
        } catch (RequestException $e) {
            Log::error('OpenAI API Error', ['message' => $e->getMessage()]);
            return new class {
                public function successful(): bool  { return false; }
                public function json(): array       { return []; }
                public function body(): string      { return 'Connection failed'; }
            };
        }
    }

    private function ask(string $prompt, int $timeout = 30, int $maxTokens = 1024): string
    {
        $response = $this->makeRequest([
            ['role' => 'user', 'content' => $prompt],
        ], $timeout, $maxTokens);

        if ($response->successful()) {
            return $response->json()['choices'][0]['message']['content'] ?? '';
        }
        return '';
    }

    // -------------------------------------------------------------------------
    // AIServiceInterface implementation
    // -------------------------------------------------------------------------

    public function testConnection(): array
    {
        try {
            $response = $this->makeRequest([
                ['role' => 'user', 'content' => 'Hello, just testing connection'],
            ], 10, 10);

            return $response->successful()
                ? ['success' => true,  'message' => 'Koneksi OpenAI berhasil!']
                : ['success' => false, 'message' => 'Gagal connect: ' . $response->body()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function translateDirectlyWithAI(string $text, string $direction = 'belitung_to_indonesia'): array
    {
        try {
            $prompt      = $this->buildTranslatePrompt($text, $direction);
            $translation = $this->ask($prompt, 30, 600);

            if ($translation === '') {
                return ['success' => false, 'error' => 'API Error'];
            }

            $translation = $this->postProcessAIResult($translation, $text);
            $context     = $this->createOptimizedContext($text, $direction);

            return [
                'success'            => true,
                'input'              => $text,
                'translation'        => $translation,
                'direction'          => $direction,
                'method'             => 'ai_with_optimized_context',
                'confidence'         => 'medium',
                'context_words_used' => substr_count($context, "\n") - 1,
                'context_preview'    => substr($context, 0, 200) . '...',
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Exception: ' . $e->getMessage()];
        }
    }

    public function translateToEnglish(string $text): array
    {
        $prompt      = "Translate this Indonesian text to English. Only return the translation, no explanation. Maintain proper capitalization: \"{$text}\"";
        $translation = $this->ask($prompt, 30, 600);

        if ($translation === '') {
            return ['success' => false, 'error' => 'Translation failed'];
        }

        return ['success' => true, 'translation' => $this->postProcessAIResult($translation, $text)];
    }

    public function translateToIndonesian(string $text): array
    {
        $prompt      = "Translate this English text to Indonesian. Only return the translation, no explanation. Maintain proper capitalization: \"{$text}\"";
        $translation = $this->ask($prompt, 30, 600);

        if ($translation === '') {
            return ['success' => false, 'error' => 'Translation failed'];
        }

        return ['success' => true, 'translation' => $this->postProcessAIResult($translation, $text)];
    }

    public function chatWithContext(string $userMessage, string $contextType = 'general'): array
    {
        try {
            $prompt = $this->buildChatPrompt($userMessage, $contextType);
            $answer = $this->ask($prompt);

            if ($answer === '') {
                return ['success' => false, 'error' => 'API Error'];
            }

            return [
                'success'          => true,
                'user_message'     => $userMessage,
                'assistant_answer' => trim($answer),
                'context_type'     => $contextType,
                'timestamp'        => now(),
            ];
        } catch (\Exception $e) {
            Log::error('Chatbot Error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function chatWithHistory(string $userMessage, array $conversationHistory = [], string $contextType = 'general'): array
    {
        try {
            $historyText = '';
            foreach (array_slice($conversationHistory, -5) as $msg) {
                $historyText .= "User: {$msg['user']}\nAssistant: {$msg['assistant']}\n\n";
            }

            $prompt = $this->buildChatPrompt($userMessage, $contextType, $historyText);
            $answer = $this->ask($prompt);

            if ($answer === '') {
                return ['success' => false, 'error' => 'API Error'];
            }

            return [
                'success'          => true,
                'user_message'     => $userMessage,
                'assistant_answer' => trim($answer),
                'timestamp'        => now(),
            ];
        } catch (\Exception $e) {
            Log::error('Chatbot Error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function extractTextFromImage(string $imagePath, int $maxRetries = 3): array
    {
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                $imageData = base64_encode(file_get_contents($imagePath));
                $mimeType  = mime_content_type($imagePath);

                $response = $this->makeRequest([
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => "Extract ALL text from this image. Return ONLY the extracted text, nothing else. Preserve line breaks and formatting. Do not add explanations or labels.",
                            ],
                            [
                                'type'      => 'image_url',
                                'image_url' => ['url' => "data:{$mimeType};base64,{$imageData}"],
                            ],
                        ],
                    ],
                ], 120, 4096);

                if ($response->successful()) {
                    $extractedText = trim($response->json()['choices'][0]['message']['content'] ?? '');
                    return ['success' => true, 'text' => $extractedText];
                }

                return ['success' => false, 'error' => 'API Error: ' . $response->body()];

            } catch (RequestException $e) {
                $attempt++;
                $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;

                if ($statusCode === 429 || $statusCode === 503) {
                    if ($attempt >= $maxRetries) {
                        return ['success' => false, 'error' => 'Server sedang sibuk. Coba lagi dalam beberapa menit.'];
                    }
                    sleep(pow(2, $attempt));
                    continue;
                }

                return ['success' => false, 'error' => 'API Error: ' . $e->getMessage()];
            } catch (\Exception $e) {
                Log::error('OCR Extract Exception', ['message' => $e->getMessage()]);
                return ['success' => false, 'error' => $e->getMessage()];
            }
        }

        return ['success' => false, 'error' => 'Max retries exceeded'];
    }

    public function testTranslateDetails(string $text, string $direction = 'belitung_to_indonesia'): array
    {
        $result = [
            'original_text' => $text,
            'cleaned_text'  => $this->cleanText($text),
            'cleaned_words' => explode(' ', $this->cleanText($text)),
        ];

        $context = $this->createOptimizedContext($text, $direction);
        $result['context_words']   = substr_count($context, "\n") - 1;
        $result['context_preview'] = substr($context, 0, 500) . '...';
        $result['context_full']    = strlen($context) > 10000 ? 'Too long to display' : $context;

        $highRelevant = $this->findHighRelevantWords($text, $direction);
        $result['high_relevant_count']  = $highRelevant->count();
        $result['high_relevant_sample'] = $highRelevant->take(5)->map(fn($i) => $i->bahasa_belitung . ' → ' . $i->bahasa_indonesia)->toArray();

        $result['final_result'] = $this->translateDirectlyWithAI($text, $direction);

        return $result;
    }
}
