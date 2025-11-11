<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\DatasetTranslate;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class GeminiService
{
    private $apiKey;
    private $apiUrl;
    private $client;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
        $this->client = new Client();
    }

    /**
     * Make request to Gemini API using GuzzleHttp
     */
    private function makeRequest($data, $timeout = 30)
    {
        try {
            $response = $this->client->post($this->apiUrl . '?key=' . $this->apiKey, [
                'json' => $data,
                'timeout' => $timeout,
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'verify' => false,
            ]);

            return new class($response) {
                private $response;
                public function __construct($response) {
                    $this->response = $response;
                }
                public function successful() {
                    return $this->response->getStatusCode() === 200;
                }
                public function json() {
                    return json_decode((string)$this->response->getBody(), true);
                }
                public function body() {
                    return (string)$this->response->getBody();
                }
            };
        } catch (RequestException $e) {
                Log::error('Gemini API Error', [
                'message' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody() : null,
                'request' => $e->getRequest()->getUri()
            ]);
            return new class {
                public function successful() { return false; }
                public function json() { return []; }
                public function body() { return 'Connection failed'; }
            };
        }
    }

    private function cleanText($text)
    {
        $cleaned = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        return trim(strtolower($cleaned));
    }

    private function cleanWord($word)
    {
        $cleaned = preg_replace('/^[^\p{L}\p{N}]+|[^\p{L}\p{N}]+$/u', '', $word);
        return trim(strtolower($cleaned));
    }

    private function postProcessAIResult($text, $originalText)
    {
        $text = trim($text);
        $text = preg_replace('/^(Terjemahan:|Translation:)/i', '', $text);
        $text = trim($text);
        $text = $this->matchOriginalCapitalizationPattern($text, $originalText);
        return $text;
    }

    private function matchOriginalCapitalizationPattern($translation, $originalText)
    {
        if (preg_match('/^\p{Lu}/u', $originalText)) {
            $translation = ucfirst($translation);
        }
        $translation = preg_replace_callback('/([.!?])\s+(\p{Ll})/u', function($matches) {
            return $matches[1] . ' ' . strtoupper($matches[2]);
        }, $translation);
        return $translation;
    }

    public function testConnection()
    {
        try {
            $response = $this->makeRequest([
                'contents' => [
                    [
                        'parts' => [
                            ['text' => 'Hello, just testing connection']
                        ]
                    ]
                ]
            ], 10);

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Koneksi Gemini berhasil!'];
            } else {
                return ['success' => false, 'message' => 'Gagal connect: ' . $response->body()];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function translateDirectlyWithAI($text, $direction = 'belitung_to_indonesia')
    {
        try {
            $context = $this->createOptimizedContext($text, $direction);
            
            if ($direction == 'belitung_to_indonesia') {
                $prompt = "ATURAN MUTLAK: GUNAKAN KAMUS DATABASE INI SEBAGAI PRIORITAS UTAMA!\n\nKAMUS BAHASA MELAYU BELITUNG KE INDONESIA (WAJIB DIPAKAI):\n{$context}\n\nINSTRUKSI KETAT:\n1. CEK SETIAP KATA dalam kamus di atas TERLEBIH DAHULU\n2. JIKA kata ADA dalam kamus, WAJIB gunakan terjemahan persis dari kamus\n3. JIKA kata TIDAK ADA dalam kamus, gunakan pengetahuan umum bahasa Melayu Belitung\n4. Untuk kalimat utuh, prioritaskan makna dari kamus tapi sesuaikan konteks\n5. Jangan ubah kata yang sudah ada terjemahannya di kamus\n6. Hasil harus natural tapi tetap akurat sesuai kamus\n7. ABAIKAN tanda baca saat mencari di kamus\n8. PERTAHANKAN format kapitalisasi yang sesuai standar bahasa Indonesia\n\nTeks untuk diterjemahkan: \"{$text}\"\n\nTerjemahan (HANYA hasil, tanpa penjelasan):";
            } else {
                $prompt = "ATURAN MUTLAK: GUNAKAN KAMUS DATABASE INI SEBAGAI PRIORITAS UTAMA!\n\nKAMUS BAHASA INDONESIA KE MELAYU BELITUNG (WAJIB DIPAKAI):\n{$context}\n\nINSTRUKSI KETAT:\n1. CEK SETIAP KATA dalam kamus di atas TERLEBIH DAHULU\n2. JIKA kata ADA dalam kamus, WAJIB gunakan terjemahan persis dari kamus\n3. JIKA kata TIDAK ADA dalam kamus, gunakan pengetahuan umum bahasa Melayu Belitung\n4. Untuk kalimat utuh, prioritaskan makna dari kamus tapi sesuaikan konteks\n5. Jangan ubah kata yang sudah ada terjemahannya di kamus\n6. Hasil harus natural tapi tetap akurat sesuai kamus\n7. ABAIKAN tanda baca saat mencari di kamus\n8. PERTAHANKAN format kapitalisasi yang sesuai standar bahasa Belitung\n\nTeks untuk diterjemahkan: \"{$text}\"\n\nTerjemahan (HANYA hasil, tanpa penjelasan):";
            }

            $response = $this->makeRequest([
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $translation = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                $translation = $this->postProcessAIResult($translation, $text);
                
                return [
                    'success' => true,
                    'input' => $text,
                    'translation' => $translation,
                    'direction' => $direction,
                    'method' => 'ai_with_optimized_context',
                    'confidence' => 'medium'
                ];
            } else {
                return ['success' => false, 'error' => 'API Error: ' . $response->body()];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Exception: ' . $e->getMessage()];
        }
    }

    private function createOptimizedContext($text, $direction = 'belitung_to_indonesia')
    {
        $cacheKey = "optimized_context_v5_" . md5($text . $direction);
        return Cache::remember($cacheKey, 3600, function() use ($text, $direction) {
            $highRelevantWords = $this->findHighRelevantWords($text, $direction);
            $similarWords = $this->findSimilarWords($text, $direction);
            $commonWords = $this->getCommonWords(50);
            
            $allWords = $highRelevantWords->merge($similarWords)->merge($commonWords)->unique('bahasa_belitung')->take(150);
            
            if ($allWords->isEmpty()) {
                return "Tidak ada data kamus tersedia.";
            }

            if ($direction == 'belitung_to_indonesia') {
                $context = "KAMUS PRIORITAS BAHASA MELAYU BELITUNG KE INDONESIA:\n";
                foreach ($allWords as $item) {
                    $context .= strtolower(trim($item->bahasa_belitung)) . " → " . strtolower(trim($item->bahasa_indonesia)) . "\n";
                }
            } else {
                $context = "KAMUS PRIORITAS BAHASA INDONESIA KE MELAYU BELITUNG:\n";
                foreach ($allWords as $item) {
                    $context .= strtolower(trim($item->bahasa_indonesia)) . " → " . strtolower(trim($item->bahasa_belitung)) . "\n";
                }
            }
            return $context;
        });
    }

    private function findHighRelevantWords($text, $direction = 'belitung_to_indonesia', $limit = 50)
    {
        $cleanedText = $this->cleanText($text);
        $words = explode(' ', $cleanedText);
        $relevantData = collect();
        
        foreach ($words as $word) {
            $cleanWord = $this->cleanWord($word);
            if (strlen($cleanWord) < 2) continue;
            
            if ($direction == 'belitung_to_indonesia') {
                $exactMatches = DatasetTranslate::whereRaw('LOWER(bahasa_belitung) = ?', [$cleanWord])->limit(5)->get(['bahasa_belitung', 'bahasa_indonesia']);
                $startsWithMatches = DatasetTranslate::whereRaw('LOWER(bahasa_belitung) LIKE ?', [$cleanWord . '%'])->limit(10)->get(['bahasa_belitung', 'bahasa_indonesia']);
                $containsMatches = DatasetTranslate::whereRaw('LOWER(bahasa_belitung) LIKE ?', ['%' . $cleanWord . '%'])->where('bahasa_belitung', '!=', $cleanWord)->limit(10)->get(['bahasa_belitung', 'bahasa_indonesia']);
                $relevantData = $relevantData->merge($exactMatches)->merge($startsWithMatches)->merge($containsMatches);
            } else {
                $exactMatches = DatasetTranslate::whereRaw('LOWER(bahasa_indonesia) = ?', [$cleanWord])->limit(5)->get(['bahasa_belitung', 'bahasa_indonesia']);
                $startsWithMatches = DatasetTranslate::whereRaw('LOWER(bahasa_indonesia) LIKE ?', [$cleanWord . '%'])->limit(10)->get(['bahasa_belitung', 'bahasa_indonesia']);
                $containsMatches = DatasetTranslate::whereRaw('LOWER(bahasa_indonesia) LIKE ?', ['%' . $cleanWord . '%'])->where('bahasa_indonesia', '!=', $cleanWord)->limit(10)->get(['bahasa_belitung', 'bahasa_indonesia']);
                $relevantData = $relevantData->merge($exactMatches)->merge($startsWithMatches)->merge($containsMatches);
            }
        }
        return $relevantData->unique('bahasa_belitung')->take($limit);
    }

    private function findSimilarWords($text, $direction = 'belitung_to_indonesia', $limit = 30)
    {
        $cleanedText = $this->cleanText($text);
        $textLength = strlen($cleanedText);
        if ($textLength < 5) return collect();
        
        $similarLengthWords = DatasetTranslate::whereBetween(
            \DB::raw('CHAR_LENGTH(' . ($direction == 'belitung_to_indonesia' ? 'bahasa_belitung' : 'bahasa_indonesia') . ')'),
            [strlen($cleanedText) - 2, strlen($cleanedText) + 2]
        )->inRandomOrder()->limit($limit)->get(['bahasa_belitung', 'bahasa_indonesia']);
        return $similarLengthWords;
    }

    private function getCommonWords($count = 50)
    {
        return DatasetTranslate::whereIn('bahasa_belitung', ['aku', 'kamu', 'dia', 'kita', 'mereka', 'ini', 'itu', 'di', 'ke', 'dari', 'dan', 'atau', 'tapi', 'kalau', 'karena', 'sudah', 'belum', 'akan', 'sedang', 'telah', 'bisa', 'tidak', 'ya', 'baik', 'bagus'])
            ->get(['bahasa_belitung', 'bahasa_indonesia'])
            ->merge(DatasetTranslate::whereNotIn('bahasa_belitung', ['aku', 'kamu', 'dia', 'kita', 'mereka', 'ini', 'itu', 'di', 'ke', 'dari', 'dan', 'atau', 'tapi', 'kalau', 'karena', 'sudah', 'belum', 'akan', 'sedang', 'telah', 'bisa', 'tidak', 'ya', 'baik', 'bagus'])
                ->inRandomOrder()->limit($count - 25)->get(['bahasa_belitung', 'bahasa_indonesia']));
    }

    public function getDatabaseStats()
    {
        return [
            'total_words' => DatasetTranslate::count(),
            'belitung_sample' => DatasetTranslate::limit(5)->pluck('bahasa_belitung')->toArray(),
            'indonesia_sample' => DatasetTranslate::limit(5)->pluck('bahasa_indonesia')->toArray(),
        ];
    }

    public function testTranslateDetails($text, $direction = 'belitung_to_indonesia')
    {
        $result = [];
        $result['original_text'] = $text;
        $result['cleaned_text'] = $this->cleanText($text);
        $result['cleaned_words'] = explode(' ', $this->cleanText($text));
        $context = $this->createOptimizedContext($text, $direction);
        $result['context_words'] = substr_count($context, "\n") - 1;
        $result['context_preview'] = substr($context, 0, 500) . '...';
        $highRelevant = $this->findHighRelevantWords($text, $direction);
        $result['high_relevant_count'] = $highRelevant->count();
        $translation = $this->translateDirectlyWithAI($text, $direction);
        $result['final_result'] = $translation;
        return $result;
    }

    public function loadKamusData($limit = 100)
    {
        try {
            $kamusData = DatasetTranslate::limit($limit)->get(['bahasa_belitung', 'bahasa_indonesia'])->toArray();
            return $kamusData;
        } catch (\Exception $e) {
            Log::error('Error loading dataset data: ' . $e->getMessage());
            return [];
        }
    }

    public function testKamusData()
    {
        $data = $this->loadKamusData(10);
        return ['total_loaded' => count($data), 'sample_data' => $data];
    }

    public function translateToEnglish($text)
    {
        $prompt = "Translate this Indonesian text to English. Only return the translation, no explanation. Maintain proper capitalization: \"{$text}\"";
        
        $response = $this->makeRequest([
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $translation = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $translation = $this->postProcessAIResult($translation, $text);
            return ['success' => true, 'translation' => $translation];
        }
        return ['success' => false, 'error' => 'Translation failed'];
    }

    public function chatWithContext($userMessage, $contextType = 'general')
    {
        try {
            $context = $this->getWebsiteContext($contextType);
            $prompt = "Anda adalah asisten AI untuk website Bilikbecakap - Platform Pelestarian Budaya dan Bahasa Melayu Belitung.\n\nINFORMASI WEBSITE:\n{$context}\n\nPertanyaan User: \"{$userMessage}\"\n\nJawaban (natural dan informatif):";

            $response = $this->makeRequest([
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $answer = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                return [
                    'success' => true,
                    'user_message' => $userMessage,
                    'assistant_answer' => trim($answer),
                    'context_type' => $contextType,
                    'timestamp' => now()
                ];
            } else {
                return ['success' => false, 'error' => 'API Error: ' . $response->body()];
            }
        } catch (\Exception $e) {
            Log::error('Chatbot Error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Exception: ' . $e->getMessage()];
        }
    }

    private function getWebsiteContext($contextType = 'general')
    {
        $cacheKey = "website_context_{$contextType}";
        return Cache::remember($cacheKey, 3600, function() use ($contextType) {
            switch ($contextType) {
                case 'kamus':
                    return $this->getKamusContext();
                case 'pembelajaran':
                    return $this->getBelajarContext();
                case 'penerjemah':
                    return $this->getPenerjemahContext();
                case 'tentang':
                    return $this->getTentangContext();
                default:
                    return $this->getGeneralContext();
            }
        });
    }

    private function getGeneralContext()
    {
        return "NAMA WEBSITE: Bilikbecakap\nTUJUAN: Platform Pelestarian Budaya dan Bahasa Melayu Belitung\nFITUR UTAMA: Kamus Digital, Penerjemah, Pembelajaran, Galeri, Artikel\nLOKASI: Senyubuk, Belitung Timur, Indonesia";
    }

    private function getKamusContext()
    {
        $totalWords = \App\Models\DatasetTranslate::count();
        return "KAMUS DIGITAL BILIKBECAKAP - Total Kata: {$totalWords}\nFITUR: Pencarian cepat, Audio pronunciation, Keterangan untuk setiap kata";
    }

    private function getPenerjemahContext()
    {
        return "PENERJEMAH BILIKBECAKAP\nFITUR: Terjemahkan Indonesia ↔ Melayu Belitung dengan AI\nPowered by Artificial Intelligence + Database Lokal";
    }

    private function getBelajarContext()
    {
        return "PEMBELAJARAN BILIKBECAKAP\nFITUR: Modul pembelajaran interaktif, Quiz, Topik budaya Belitung";
    }

    private function getTentangContext()
    {
        return "TENTANG BILIKBECAKAP\nBilikbecakap adalah platform untuk pelestarian bahasa dan budaya Melayu Belitung\nDIDUKUNG: Kementerian Kebudayaan, Pemerintah Belitung Timur";
    }

    public function chatWithHistory($userMessage, $conversationHistory = [], $contextType = 'general')
    {
        try {
            $context = $this->getWebsiteContext($contextType);
            $historyText = "";
            foreach ($conversationHistory as $msg) {
                $historyText .= "User: {$msg['user']}\nAssistant: {$msg['assistant']}\n\n";
            }
            $prompt = "Anda adalah asisten AI untuk website Bilikbecakap.\n\nINFORMASI WEBSITE:\n{$context}\n\nRIWAYAT PERCAKAPAN:\n{$historyText}\n\nPertanyaan User Terbaru: \"{$userMessage}\"\n\nJawaban:";

            $response = $this->makeRequest([
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $answer = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                return [
                    'success' => true,
                    'user_message' => $userMessage,
                    'assistant_answer' => trim($answer),
                    'timestamp' => now()
                ];
            } else {
                return ['success' => false, 'error' => 'API Error'];
            }
        } catch (\Exception $e) {
            Log::error('Chatbot Error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}