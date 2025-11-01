<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class TranslateController extends Controller implements HasMiddleware
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
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
     * NEW: Clean text from punctuation and special characters (untuk pencarian)
     */
    private function cleanText($text)
    {
        $cleaned = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        return trim(strtolower($cleaned));
    }

    /**
     * NEW: Clean individual word from punctuation (untuk pencarian)
     */
    private function cleanWord($word)
    {
        $cleaned = preg_replace('/^[^\p{L}\p{N}]+|[^\p{L}\p{N}]+$/u', '', $word);
        return trim(strtolower($cleaned));
    }

    /**
     * NEW: Parse text dengan preserving punctuation
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
                    $result[] = [
                        'type' => 'word',
                        'original' => $token,
                        'clean' => $cleanWord,
                        'punctuation' => $punctuation
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
     * NEW: Reconstruct text dengan translation dan punctuation
     */
    private function reconstructWithPunctuation($tokens, $translations)
    {
        $result = '';
        $translationIndex = 0;
        
        foreach ($tokens as $token) {
            if ($token['type'] === 'word') {
                // Gunakan translation jika ada, otherwise original clean word
                if (isset($translations[$translationIndex])) {
                    $result .= $translations[$translationIndex] . $token['punctuation'];
                } else {
                    $result .= $token['clean'] . $token['punctuation'];
                }
                $translationIndex++;
            } elseif ($token['type'] === 'space') {
                $result .= $token['original'];
            } else {
                // punctuation only
                $result .= $token['original'];
            }
        }
        
        return $result;
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
            'text' => 'required|string',
            'direction' => 'required|in:melayu_to_indonesia,indonesia_to_melayu',
            'method' => 'required|in:hybrid,rule_based',
        ], [
            'text.required' => 'Teks yang akan diterjemahkan wajib diisi.',
            'text.max' => 'Teks maksimal 1000 karakter.',
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
            $processingTime = round(($endTime - $startTime) * 1000, 2);

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
     * IMPROVED METHOD 1: HYBRID dengan punctuation preservation
     */
    private function translateHybrid($text, $direction)
    {
        $cleanText = trim($text);
        $wordCount = str_word_count($cleanText);

        try {
            // 1. Coba direct search dulu (exact match) - dengan clean text
            $directMatch = $this->directSearchWithCleaning($cleanText, $direction);
            if ($directMatch) {
                return [
                    'success' => true,
                    'input' => $cleanText,
                    'translation' => $directMatch,
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
                    return [
                        'success' => true,
                        'input' => $cleanText,
                        'translation' => $fuzzyMatch['translation'],
                        'direction' => $direction,
                        'method' => 'hybrid_fuzzy',
                        'confidence' => 'medium',
                        'matched_terms' => [$fuzzyMatch['matched_term']],
                        'ai_used' => false,
                        'translation_rate' => 100.0
                    ];
                }
            }

            // 3. Multi-word dengan punctuation preservation
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
     * IMPROVED: Word-by-word dengan punctuation preservation
     */
    private function translateWordByWordHybridWithPunctuation($text, $direction)
    {
        // Parse text dengan punctuation
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
                $translations[] = $directMatch;
                $matchedTerms[] = $word . ' → ' . $directMatch;
                $translatedCount++;
                continue;
            }

            // Coba fuzzy match
            $fuzzyMatch = $this->fuzzySearch($word, $direction, 0.8);
            if ($fuzzyMatch) {
                $translations[] = $fuzzyMatch['translation'];
                $matchedTerms[] = $word . ' → ' . $fuzzyMatch['translation'] . ' (fuzzy)';
                $translatedCount++;
                continue;
            }

            // No match
            $translations[] = $word;
            $untranslatedWords[] = $word;
        }

        // Reconstruct dengan punctuation
        $finalTranslation = $this->reconstructWithPunctuation($tokens, $translations);

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
     * METHOD 2: Rule-based dengan punctuation preservation
     */
    private function translateRuleBased($text, $direction)
    {
        $cleanText = trim($text);

        try {
            // 1. Direct search
            $directMatch = $this->directSearchWithCleaning($cleanText, $direction);
            if ($directMatch) {
                return [
                    'success' => true,
                    'input' => $cleanText,
                    'translation' => $directMatch,
                    'direction' => $direction,
                    'method' => 'rule_direct',
                    'confidence' => 'high',
                    'translation_rate' => 100.0,
                    'ai_used' => false
                ];
            }

            // 2. Word-by-word dengan punctuation
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
     * IMPROVED: Word-by-word dengan punctuation preservation untuk rule-based
     */
    private function translateWordByWordWithPunctuation($text, $direction)
    {
        // Parse text dengan punctuation
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
                $translations[] = $directMatch;
                $matchedTerms[] = $word . ' → ' . $directMatch;
                $translatedCount++;
                continue;
            }

            // Fuzzy match
            $fuzzyMatch = $this->fuzzySearch($word, $direction);
            if ($fuzzyMatch) {
                $translations[] = $fuzzyMatch['translation'];
                $matchedTerms[] = $word . ' → ' . $fuzzyMatch['translation'] . ' (fuzzy: ' . $fuzzyMatch['matched_term'] . ')';
                $translatedCount++;
                continue;
            }

            // No match
            $translations[] = $word;
            $untranslatedWords[] = $word;
        }

        // Reconstruct dengan punctuation
        $finalTranslation = $this->reconstructWithPunctuation($tokens, $translations);

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

    // Helper methods tetap sama
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
        
        if ($direction == 'melayu_to_indonesia') {
            $result = \App\Models\Kamus::where('status', 1)
                ->whereRaw('LOWER(bahasa_melayu) = ?', [$cleanText])
                ->first();
            return $result ? $result->bahasa_indonesia : null;
        } else {
            $result = \App\Models\Kamus::where('status', 1)
                ->whereRaw('LOWER(bahasa_indonesia) = ?', [$cleanText])
                ->first();
            return $result ? $result->bahasa_melayu : null;
        }
    }

    private function fuzzySearch($text, $direction, $threshold = 0.6)
    {
        $cleanText = trim(strtolower($text));
        
        if (strlen($cleanText) < 3) return null;
        
        if ($direction == 'melayu_to_indonesia') {
            $result = \App\Models\Kamus::where('status', 1)
                ->where('bahasa_melayu', 'LIKE', "%{$cleanText}%")
                ->orderByRaw('LENGTH(bahasa_melayu) ASC')
                ->first();
            
            if ($result) {
                $similarity = $this->calculateSimilarity($cleanText, strtolower($result->bahasa_melayu));
                if ($similarity >= $threshold) {
                    return [
                        'translation' => $result->bahasa_indonesia,
                        'matched_term' => $result->bahasa_melayu,
                        'similarity' => $similarity
                    ];
                }
            }
        } else {
            $result = \App\Models\Kamus::where('status', 1)
                ->where('bahasa_indonesia', 'LIKE', "%{$cleanText}%")
                ->orderByRaw('LENGTH(bahasa_indonesia) ASC')
                ->first();
            
            if ($result) {
                $similarity = $this->calculateSimilarity($cleanText, strtolower($result->bahasa_indonesia));
                if ($similarity >= $threshold) {
                    return [
                        'translation' => $result->bahasa_melayu,
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

    // Rest methods sama seperti sebelumnya...
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