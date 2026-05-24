<?php

namespace App\Http\Controllers;

use App\Contracts\AIServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class TranslateController extends Controller implements HasMiddleware
{
    protected AIServiceInterface $geminiService;

    public function __construct(AIServiceInterface $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
        ];
    }

    /**
     * Clean text from punctuation and special characters (untuk pencarian)
     */
    private function cleanText($text)
    {
        $cleaned = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        return trim(strtolower($cleaned));
    }

    /**
     * Clean individual word from punctuation (untuk pencarian)
     */
    private function cleanWord($word)
    {
        $cleaned = preg_replace('/^[^\p{L}\p{N}]+|[^\p{L}\p{N}]+$/u', '', $word);
        return trim(strtolower($cleaned));
    }

    /**
     * Parse text dengan preserving punctuation dan capitalization
     */
    private function parseTextWithPunctuation($text)
    {
        // Split berdasarkan whitespace tapi preserve punctuation
        $tokens = preg_split('/(\s+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = [];
        
        foreach ($tokens as $token) {
            if (trim($token) === '') {
                $result[] = ['type' => 'space', 'original' => $token, 'clean' => ''];
            } else {
                $cleanWord = $this->cleanWord($token);
                if (strlen($cleanWord) >= 2) {
                    // Extract punctuation
                    $punctuation = preg_replace('/[\p{L}\p{N}\s]/u', '', $token);
                    
                    // Detect if original word starts with capital
                    $isCapitalized = preg_match('/^\p{Lu}/u', $token);
                    
                    $result[] = [
                        'type' => 'word',
                        'original' => $token,
                        'clean' => $cleanWord,
                        'punctuation' => $punctuation,
                        'is_capitalized' => $isCapitalized
                    ];
                } else {
                    // Kata terlalu pendek atau hanya punctuation
                    $result[] = ['type' => 'punctuation', 'original' => $token, 'clean' => ''];
                }
            }
        }
        
        return $result;
    }

    /**
     * Apply proper capitalization based on context
     */
    private function applyProperCapitalization($translation, $originalCapitalized, $isFirstWord = false, $afterPunctuation = false)
    {
        // Convert to lowercase first
        $translation = strtolower($translation);
        
        // Capitalize if:
        // 1. It's the first word of sentence
        // 2. Original word was capitalized (proper nouns, etc)
        // 3. After sentence-ending punctuation
        if ($isFirstWord || $originalCapitalized || $afterPunctuation) {
            $translation = ucfirst($translation);
        }
        
        return $translation;
    }

    /**
     * Check if punctuation ends a sentence
     */
    private function isSentenceEndingPunctuation($punctuation)
    {
        return preg_match('/[.!?]/', $punctuation);
    }

    /**
     * Reconstruct text dengan translation, punctuation, dan proper capitalization
     */
    private function reconstructWithPunctuation($tokens, $translations)
    {
        $result = '';
        $translationIndex = 0;
        $isFirstWord = true;
        $afterSentenceEnd = false;
        
        foreach ($tokens as $token) {
            if ($token['type'] === 'word') {
                // Gunakan translation jika ada, otherwise original clean word
                if (isset($translations[$translationIndex])) {
                    $translatedWord = $translations[$translationIndex];
                    
                    // Apply proper capitalization
                    $finalWord = $this->applyProperCapitalization(
                        $translatedWord, 
                        $token['is_capitalized'], 
                        $isFirstWord, 
                        $afterSentenceEnd
                    );
                    
                    $result .= $finalWord . $token['punctuation'];
                } else {
                    // Fallback to original word with proper capitalization
                    $finalWord = $this->applyProperCapitalization(
                        $token['clean'], 
                        $token['is_capitalized'], 
                        $isFirstWord, 
                        $afterSentenceEnd
                    );
                    
                    $result .= $finalWord . $token['punctuation'];
                }
                
                // Check if this word ends with sentence-ending punctuation
                $afterSentenceEnd = $this->isSentenceEndingPunctuation($token['punctuation']);
                $isFirstWord = false;
                $translationIndex++;
                
            } elseif ($token['type'] === 'space') {
                $result .= $token['original'];
            } else {
                // punctuation only
                $result .= $token['original'];
                
                // Check if it's sentence-ending punctuation
                if ($this->isSentenceEndingPunctuation($token['original'])) {
                    $afterSentenceEnd = true;
                }
            }
        }
        
        return $result;
    }

    /**
     * Post-process untuk memastikan kapitalisasi yang benar pada hasil final
     */
    private function postProcessCapitalization($text)
    {
        // Capitalize first letter of the entire text
        $text = ucfirst($text);
        
        // Capitalize after sentence-ending punctuation followed by space
        $text = preg_replace_callback('/([.!?])\s+(\p{Ll})/u', function($matches) {
            return $matches[1] . ' ' . strtoupper($matches[2]);
        }, $text);
        
        return $text;
    }

    /**
     * Tampilkan halaman test translate
     */
    public function index()
    {
        $connectionTest = $this->geminiService->testConnection();
        $databaseStats = $this->geminiService->getDatabaseStats();

        return Inertia::render('TestTranslate/Index', [
            'connectionStatus' => $connectionTest,
            'databaseStats' => $databaseStats,
            'user' => auth()->user(),
        ]);
    }

    /**
     * Proses translate text dengan 2 metode
     */
    public function translate(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:10000',
            'direction' => 'required|in:belitung_to_indonesia,indonesia_to_belitung',
            'method' => 'required|in:hybrid,rule_based',
        ], [
            'text.required' => 'Teks yang akan diterjemahkan wajib diisi.',
            'text.max' => 'Teks maksimal 10000 karakter.',
            'direction.required' => 'Arah terjemahan wajib dipilih.',
            'direction.in' => 'Arah terjemahan tidak valid.',
            'method.required' => 'Metode translate wajib dipilih.',
            'method.in' => 'Metode translate tidak valid.',
        ]);

        try {
            $startTime = microtime(true);
            
            if ($request->method === 'hybrid') {
                $result = $this->translateHybrid($request->text, $request->direction);
            } else {
                $result = $this->translateRuleBased($request->text, $request->direction);
            }
            
            $endTime = microtime(true);
            $processingTime = round(($endTime - $startTime) * 10000, 2);

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'input_text' => $request->text,
                    'direction' => $request->direction,
                    'selected_method' => $request->method,
                    'actual_method' => $result['method'] ?? 'unknown',
                    'confidence' => $result['confidence'] ?? 'unknown',
                    'processing_time_ms' => $processingTime,
                    'success' => $result['success'] ?? false,
                    'word_count' => str_word_count(trim($request->text)),
                    'translation_rate' => $result['translation_rate'] ?? null,
                ])
                ->log('Translation request processed');

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'input' => $result['input'],
                        'translation' => $result['translation'],
                        'direction' => $result['direction'],
                        'method' => $result['method'],
                        'confidence' => $result['confidence'],
                        'processing_time_ms' => $processingTime,
                        'matched_terms' => $result['matched_terms'] ?? null,
                        'untranslated_words' => $result['untranslated_words'] ?? null,
                        'context_words_used' => $result['context_words_used'] ?? null,
                        'word_count' => str_word_count(trim($request->text)),
                        'selected_method' => $request->method,
                        'translation_rate' => $result['translation_rate'] ?? null,
                        'ai_used' => $result['ai_used'] ?? false,
                    ],
                    'message' => 'Terjemahan berhasil!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menerjemahkan: ' . ($result['error'] ?? 'Unknown error'),
                    'processing_time_ms' => $processingTime,
                ], 422);
            }

        } catch (\Exception $e) {
            Log::error('Translation error: ' . $e->getMessage(), [
                'input' => $request->text,
                'direction' => $request->direction,
                'method' => $request->method,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.',
                'error_detail' => app()->isProduction() ? null : $e->getMessage(),
            ], 500);
        }
    }

    /**
     * METHOD 1: HYBRID dengan punctuation preservation dan proper capitalization
     */
    private function translateHybrid($text, $direction)
    {
        $cleanText = trim($text);
        $wordCount = str_word_count($cleanText);

        try {
            // 1. Coba direct search dulu (exact match) - dengan clean text
            $directMatch = $this->directSearchWithCleaning($cleanText, $direction);
            if ($directMatch) {
                // Apply proper capitalization untuk direct match
                $finalTranslation = $this->postProcessCapitalization($directMatch);
                
                return [
                    'success' => true,
                    'input' => $cleanText,
                    'translation' => $finalTranslation,
                    'direction' => $direction,
                    'method' => 'hybrid_direct',
                    'confidence' => 'high',
                    'ai_used' => false,
                    'translation_rate' => 100.0
                ];
            }

            // 2. Single word dengan fuzzy search
            if ($wordCount == 1) {
                $fuzzyMatch = $this->fuzzySearchWithCleaning($cleanText, $direction);
                if ($fuzzyMatch) {
                    // Apply proper capitalization untuk single word
                    $originalCapitalized = preg_match('/^\p{Lu}/u', $cleanText);
                    $finalTranslation = $this->applyProperCapitalization(
                        $fuzzyMatch['translation'], 
                        $originalCapitalized, 
                        true
                    );
                    
                    return [
                        'success' => true,
                        'input' => $cleanText,
                        'translation' => $finalTranslation,
                        'direction' => $direction,
                        'method' => 'hybrid_fuzzy',
                        'confidence' => 'medium',
                        'matched_terms' => [$fuzzyMatch['matched_term']],
                        'ai_used' => false,
                        'translation_rate' => 100.0
                    ];
                }
            }

            // 3. Multi-word dengan punctuation preservation dan proper capitalization
            if ($wordCount > 1) {
                $wordByWordResult = $this->translateWordByWordHybridWithPunctuation($cleanText, $direction);
                
                if ($wordByWordResult['translation_rate'] >= 40) {
                    return [
                        'success' => true,
                        'input' => $cleanText,
                        'translation' => $wordByWordResult['translation'],
                        'direction' => $direction,
                        'method' => 'hybrid_word_by_word',
                        'confidence' => $wordByWordResult['confidence'],
                        'matched_terms' => $wordByWordResult['matched_terms'],
                        'untranslated_words' => $wordByWordResult['untranslated_words'],
                        'translation_rate' => $wordByWordResult['translation_rate'],
                        'ai_used' => false
                    ];
                }
            }

            // 4. Fallback ke AI
            $aiResult = $this->translateWithAI($cleanText, $direction);
            if ($aiResult['success']) {
                $aiResult['ai_used'] = true;
                $aiResult['translation_rate'] = null;
                // Apply post-processing untuk AI result
                $aiResult['translation'] = $this->postProcessCapitalization($aiResult['translation']);
            }
            return $aiResult;

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Hybrid error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Word-by-word dengan punctuation preservation dan proper capitalization
     */
    private function translateWordByWordHybridWithPunctuation($text, $direction)
    {
        // Parse text dengan punctuation dan capitalization info
        $tokens = $this->parseTextWithPunctuation($text);
        
        // Extract hanya words untuk translation
        $cleanWords = [];
        foreach ($tokens as $token) {
            if ($token['type'] === 'word') {
                $cleanWords[] = $token['clean'];
            }
        }
        
        $translations = [];
        $matchedTerms = [];
        $untranslatedWords = [];
        $totalWords = count($cleanWords);
        $translatedCount = 0;

        foreach ($cleanWords as $word) {
            if (empty($word)) {
                $translations[] = $word;
                continue;
            }

            // Coba direct match
            $directMatch = $this->directSearch($word, $direction);
            if ($directMatch) {
                // Pastikan hasil dari database dalam lowercase untuk konsistensi
                $translations[] = strtolower($directMatch);
                $matchedTerms[] = $word . ' → ' . strtolower($directMatch);
                $translatedCount++;
                continue;
            }

            // Coba fuzzy match
            $fuzzyMatch = $this->fuzzySearch($word, $direction, 0.8);
            if ($fuzzyMatch) {
                // Pastikan hasil dari database dalam lowercase untuk konsistensi
                $translations[] = strtolower($fuzzyMatch['translation']);
                $matchedTerms[] = $word . ' → ' . strtolower($fuzzyMatch['translation']) . ' (fuzzy)';
                $translatedCount++;
                continue;
            }

            // No match
            $translations[] = $word;
            $untranslatedWords[] = $word;
        }

        // Reconstruct dengan punctuation dan proper capitalization
        $finalTranslation = $this->reconstructWithPunctuation($tokens, $translations);
        
        // Apply final post-processing untuk memastikan kapitalisasi yang benar
        $finalTranslation = $this->postProcessCapitalization($finalTranslation);

        $translationRate = $totalWords > 0 ? ($translatedCount / $totalWords) * 100 : 0;
        
        if ($translationRate >= 80) {
            $confidence = 'high';
        } elseif ($translationRate >= 50) {
            $confidence = 'medium';
        } elseif ($translationRate >= 25) {
            $confidence = 'low';
        } else {
            $confidence = 'very_low';
        }

        return [
            'translation' => $finalTranslation,
            'confidence' => $confidence,
            'matched_terms' => $matchedTerms,
            'untranslated_words' => $untranslatedWords,
            'translation_rate' => round($translationRate, 1)
        ];
    }

    /**
     * METHOD 2: Rule-based dengan punctuation preservation dan proper capitalization
     */
    private function translateRuleBased($text, $direction)
    {
        $cleanText = trim($text);

        try {
            // 1. Direct search
            $directMatch = $this->directSearchWithCleaning($cleanText, $direction);
            if ($directMatch) {
                // Apply proper capitalization untuk direct match
                $finalTranslation = $this->postProcessCapitalization($directMatch);
                
                return [
                    'success' => true,
                    'input' => $cleanText,
                    'translation' => $finalTranslation,
                    'direction' => $direction,
                    'method' => 'rule_direct',
                    'confidence' => 'high',
                    'translation_rate' => 100.0,
                    'ai_used' => false
                ];
            }

            // 2. Word-by-word dengan punctuation dan proper capitalization
            $result = $this->translateWordByWordWithPunctuation($cleanText, $direction);
            
            return [
                'success' => true,
                'input' => $cleanText,
                'translation' => $result['translation'],
                'direction' => $direction,
                'method' => 'rule_word_by_word',
                'confidence' => $result['confidence'],
                'matched_terms' => $result['matched_terms'],
                'untranslated_words' => $result['untranslated_words'],
                'translation_rate' => $result['translation_rate'],
                'ai_used' => false
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Rule-based error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Word-by-word dengan punctuation preservation dan proper capitalization untuk rule-based
     */
    private function translateWordByWordWithPunctuation($text, $direction)
    {
        // Parse text dengan punctuation dan capitalization info
        $tokens = $this->parseTextWithPunctuation($text);
        
        // Extract hanya words untuk translation
        $cleanWords = [];
        foreach ($tokens as $token) {
            if ($token['type'] === 'word') {
                $cleanWords[] = $token['clean'];
            }
        }
        
        $translations = [];
        $matchedTerms = [];
        $untranslatedWords = [];
        $totalWords = count($cleanWords);
        $translatedCount = 0;

        foreach ($cleanWords as $word) {
            if (empty($word)) {
                $translations[] = $word;
                continue;
            }

            // Direct match
            $directMatch = $this->directSearch($word, $direction);
            if ($directMatch) {
                // Pastikan hasil dari database dalam lowercase untuk konsistensi
                $translations[] = strtolower($directMatch);
                $matchedTerms[] = $word . ' → ' . strtolower($directMatch);
                $translatedCount++;
                continue;
            }

            // Fuzzy match
            $fuzzyMatch = $this->fuzzySearch($word, $direction);
            if ($fuzzyMatch) {
                // Pastikan hasil dari database dalam lowercase untuk konsistensi
                $translations[] = strtolower($fuzzyMatch['translation']);
                $matchedTerms[] = $word . ' → ' . strtolower($fuzzyMatch['translation']) . ' (fuzzy: ' . $fuzzyMatch['matched_term'] . ')';
                $translatedCount++;
                continue;
            }

            // No match
            $translations[] = $word;
            $untranslatedWords[] = $word;
        }

        // Reconstruct dengan punctuation dan proper capitalization
        $finalTranslation = $this->reconstructWithPunctuation($tokens, $translations);
        
        // Apply final post-processing untuk memastikan kapitalisasi yang benar
        $finalTranslation = $this->postProcessCapitalization($finalTranslation);

        $translationRate = $totalWords > 0 ? ($translatedCount / $totalWords) : 0;
        $confidence = $translationRate >= 0.8 ? 'high' : ($translationRate >= 0.5 ? 'medium' : 'low');

        return [
            'translation' => $finalTranslation,
            'confidence' => $confidence,
            'matched_terms' => $matchedTerms,
            'untranslated_words' => $untranslatedWords,
            'translation_rate' => round($translationRate * 100, 1)
        ];
    }

    // Helper methods dengan model DatasetTranslate
    private function directSearchWithCleaning($text, $direction)
    {
        $cleanText = $this->cleanText($text);
        return $this->directSearch($cleanText, $direction);
    }

    private function fuzzySearchWithCleaning($text, $direction)
    {
        $cleanText = $this->cleanText($text);
        return $this->fuzzySearch($cleanText, $direction);
    }

    private function directSearch($text, $direction)
    {
        $cleanText = trim(strtolower($text));
        
        if ($direction == 'belitung_to_indonesia') {
            $result = \App\Models\DatasetTranslate::whereRaw('LOWER(bahasa_belitung) = ?', [$cleanText])
                ->first();
            return $result ? $result->bahasa_indonesia : null;
        } else {
            $result = \App\Models\DatasetTranslate::whereRaw('LOWER(bahasa_indonesia) = ?', [$cleanText])
                ->first();
            return $result ? $result->bahasa_belitung : null;
        }
    }

    private function fuzzySearch($text, $direction, $threshold = 0.6)
    {
        $cleanText = trim(strtolower($text));
        
        if (strlen($cleanText) < 3) return null;
        
        if ($direction == 'belitung_to_indonesia') {
            $result = \App\Models\DatasetTranslate::where('bahasa_belitung', 'LIKE', "%{$cleanText}%")
                ->orderByRaw('LENGTH(bahasa_belitung) ASC')
                ->first();
            
            if ($result) {
                $similarity = $this->calculateSimilarity($cleanText, strtolower($result->bahasa_belitung));
                if ($similarity >= $threshold) {
                    return [
                        'translation' => $result->bahasa_indonesia,
                        'matched_term' => $result->bahasa_belitung,
                        'similarity' => $similarity
                    ];
                }
            }
        } else {
            $result = \App\Models\DatasetTranslate::where('bahasa_indonesia', 'LIKE', "%{$cleanText}%")
                ->orderByRaw('LENGTH(bahasa_indonesia) ASC')
                ->first();
            
            if ($result) {
                $similarity = $this->calculateSimilarity($cleanText, strtolower($result->bahasa_indonesia));
                if ($similarity >= $threshold) {
                    return [
                        'translation' => $result->bahasa_belitung,
                        'matched_term' => $result->bahasa_indonesia,
                        'similarity' => $similarity
                    ];
                }
            }
        }
        
        return null;
    }

    private function calculateSimilarity($str1, $str2)
    {
        $len1 = strlen($str1);
        $len2 = strlen($str2);
        
        if ($len1 == 0 || $len2 == 0) {
            return 0;
        }
        
        $common = strlen($str1) - levenshtein($str1, $str2);
        return $common / max($len1, $len2);
    }

    private function translateWithAI($text, $direction)
    {
        try {
            $result = $this->geminiService->translateDirectlyWithAI($text, $direction);
            
            if ($result['success']) {
                $result['method'] = 'hybrid_ai';
                $result['confidence'] = 'medium';
            }
            
            return $result;

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'AI Translation error: ' . $e->getMessage()
            ];
        }
    }

    public function testConnection()
    {
        try {
            $result = $this->geminiService->testConnection();
            activity()
                ->causedBy(auth()->user())
                ->withProperties($result)
                ->log('Gemini API connection test');
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error testing connection: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getDatabaseStats()
    {
        try {
            $stats = $this->geminiService->getDatabaseStats();
            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting database stats: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function translateToEnglish(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
        ]);

        try {
            $result = $this->geminiService->translateToEnglish($request->text);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'translation' => $result['translation']
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem.'
            ], 500);
        }
    }
}