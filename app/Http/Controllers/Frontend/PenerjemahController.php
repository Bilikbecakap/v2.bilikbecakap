<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PenerjemahController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Tampilkan halaman penerjemah
     */
    public function index()
    {
        return Inertia::render('Frontend/Penerjemah', [
            'user' => auth()->user(),
        ]);
    }

    /**
     * Proses translate text
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
                        'word_count' => str_word_count(trim($request->text)),
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
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.',
            ], 500);
        }
    }

    /**
     * Translate ke English
     */
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

    // ============= PRIVATE HELPER METHODS =============

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

    private function parseTextWithPunctuation($text)
    {
        $tokens = preg_split('/(\s+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = [];
        
        foreach ($tokens as $token) {
            if (trim($token) === '') {
                $result[] = ['type' => 'space', 'original' => $token, 'clean' => ''];
            } else {
                $cleanWord = $this->cleanWord($token);
                if (strlen($cleanWord) >= 2) {
                    $punctuation = preg_replace('/[\p{L}\p{N}\s]/u', '', $token);
                    $isCapitalized = preg_match('/^\p{Lu}/u', $token);
                    
                    $result[] = [
                        'type' => 'word',
                        'original' => $token,
                        'clean' => $cleanWord,
                        'punctuation' => $punctuation,
                        'is_capitalized' => $isCapitalized
                    ];
                } else {
                    $result[] = ['type' => 'punctuation', 'original' => $token, 'clean' => ''];
                }
            }
        }
        
        return $result;
    }

    private function applyProperCapitalization($translation, $originalCapitalized, $isFirstWord = false, $afterPunctuation = false)
    {
        $translation = strtolower($translation);
        
        if ($isFirstWord || $originalCapitalized || $afterPunctuation) {
            $translation = ucfirst($translation);
        }
        
        return $translation;
    }

    private function isSentenceEndingPunctuation($punctuation)
    {
        return preg_match('/[.!?]/', $punctuation);
    }

    private function reconstructWithPunctuation($tokens, $translations)
    {
        $result = '';
        $translationIndex = 0;
        $isFirstWord = true;
        $afterSentenceEnd = false;
        
        foreach ($tokens as $token) {
            if ($token['type'] === 'word') {
                if (isset($translations[$translationIndex])) {
                    $translatedWord = $translations[$translationIndex];
                    
                    $finalWord = $this->applyProperCapitalization(
                        $translatedWord, 
                        $token['is_capitalized'], 
                        $isFirstWord, 
                        $afterSentenceEnd
                    );
                    
                    $result .= $finalWord . $token['punctuation'];
                } else {
                    $finalWord = $this->applyProperCapitalization(
                        $token['clean'], 
                        $token['is_capitalized'], 
                        $isFirstWord, 
                        $afterSentenceEnd
                    );
                    
                    $result .= $finalWord . $token['punctuation'];
                }
                
                $afterSentenceEnd = $this->isSentenceEndingPunctuation($token['punctuation']);
                $isFirstWord = false;
                $translationIndex++;
                
            } elseif ($token['type'] === 'space') {
                $result .= $token['original'];
            } else {
                $result .= $token['original'];
                
                if ($this->isSentenceEndingPunctuation($token['original'])) {
                    $afterSentenceEnd = true;
                }
            }
        }
        
        return $result;
    }

    private function postProcessCapitalization($text)
    {
        $text = ucfirst($text);
        
        $text = preg_replace_callback('/([.!?])\s+(\p{Ll})/u', function($matches) {
            return $matches[1] . ' ' . strtoupper($matches[2]);
        }, $text);
        
        return $text;
    }

    private function translateHybrid($text, $direction)
    {
        $cleanText = trim($text);
        $wordCount = str_word_count($cleanText);

        try {
            $directMatch = $this->directSearchWithCleaning($cleanText, $direction);
            if ($directMatch) {
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

            if ($wordCount == 1) {
                $fuzzyMatch = $this->fuzzySearchWithCleaning($cleanText, $direction);
                if ($fuzzyMatch) {
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

            $aiResult = $this->translateWithAI($cleanText, $direction);
            if ($aiResult['success']) {
                $aiResult['ai_used'] = true;
                $aiResult['translation_rate'] = null;
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

    private function translateWordByWordHybridWithPunctuation($text, $direction)
    {
        $tokens = $this->parseTextWithPunctuation($text);
        
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

            $directMatch = $this->directSearch($word, $direction);
            if ($directMatch) {
                $translations[] = strtolower($directMatch);
                $matchedTerms[] = $word . ' → ' . strtolower($directMatch);
                $translatedCount++;
                continue;
            }

            $fuzzyMatch = $this->fuzzySearch($word, $direction, 0.8);
            if ($fuzzyMatch) {
                $translations[] = strtolower($fuzzyMatch['translation']);
                $matchedTerms[] = $word . ' → ' . strtolower($fuzzyMatch['translation']) . ' (fuzzy)';
                $translatedCount++;
                continue;
            }

            $translations[] = $word;
            $untranslatedWords[] = $word;
        }

        $finalTranslation = $this->reconstructWithPunctuation($tokens, $translations);
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

    private function translateRuleBased($text, $direction)
    {
        $cleanText = trim($text);

        try {
            $directMatch = $this->directSearchWithCleaning($cleanText, $direction);
            if ($directMatch) {
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

    private function translateWordByWordWithPunctuation($text, $direction)
    {
        $tokens = $this->parseTextWithPunctuation($text);
        
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

            $directMatch = $this->directSearch($word, $direction);
            if ($directMatch) {
                $translations[] = strtolower($directMatch);
                $matchedTerms[] = $word . ' → ' . strtolower($directMatch);
                $translatedCount++;
                continue;
            }

            $fuzzyMatch = $this->fuzzySearch($word, $direction);
            if ($fuzzyMatch) {
                $translations[] = strtolower($fuzzyMatch['translation']);
                $matchedTerms[] = $word . ' → ' . strtolower($fuzzyMatch['translation']) . ' (fuzzy: ' . $fuzzyMatch['matched_term'] . ')';
                $translatedCount++;
                continue;
            }

            $translations[] = $word;
            $untranslatedWords[] = $word;
        }

        $finalTranslation = $this->reconstructWithPunctuation($tokens, $translations);
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
}