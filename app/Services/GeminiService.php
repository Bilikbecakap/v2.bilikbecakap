<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Kamus;

class GeminiService
{
    private $apiKey;
    private $apiUrl;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
    }

    /**
     * NEW: Clean text from punctuation and special characters
     */
    private function cleanText($text)
    {
        // Hapus tanda baca dan karakter khusus, tapi pertahankan spasi dan huruf
        $cleaned = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);
        // Hapus multiple spaces jadi single space
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        return trim(strtolower($cleaned));
    }

    /**
     * NEW: Clean individual word from punctuation
     */
    private function cleanWord($word)
    {
        // Hapus tanda baca di awal dan akhir kata
        $cleaned = preg_replace('/^[^\p{L}\p{N}]+|[^\p{L}\p{N}]+$/u', '', $word);
        return trim(strtolower($cleaned));
    }

    /**
     * Test koneksi ke Gemini API
     */
    public function testConnection()
    {
        try {
            $response = Http::timeout(10)->post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => 'Hello, just testing connection']
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Koneksi Gemini berhasil!'];
            } else {
                return ['success' => false, 'message' => 'Gagal connect: ' . $response->body()];
            }

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * IMPROVED: Translate dengan AI + Database Context yang Lebih Optimal
     */
    public function translateDirectlyWithAI($text, $direction = 'melayu_to_indonesia')
    {
        try {
            // Gunakan context yang lebih relevan dan fokus
            $context = $this->createOptimizedContext($text, $direction);
            
            if ($direction == 'melayu_to_indonesia') {
                $prompt = "
                    ATURAN MUTLAK: GUNAKAN KAMUS DATABASE INI SEBAGAI PRIORITAS UTAMA!

                    KAMUS BAHASA MELAYU BELITUNG KE INDONESIA (WAJIB DIPAKAI):
                    {$context}

                    INSTRUKSI KETAT:
                    1. CEK SETIAP KATA dalam kamus di atas TERLEBIH DAHULU
                    2. JIKA kata ADA dalam kamus, WAJIB gunakan terjemahan persis dari kamus
                    3. JIKA kata TIDAK ADA dalam kamus, gunakan pengetahuan umum bahasa Melayu 
                    4. Untuk kalimat utuh, prioritaskan makna dari kamus tapi sesuaikan konteks
                    5. Jangan ubah kata yang sudah ada terjemahannya di kamus
                    6. Hasil harus natural tapi tetap akurat sesuai kamus
                    7. ABAIKAN tanda baca (titik, koma, dsb) saat mencari di kamus

                    CONTOH PENGGUNAAN:
                    - Jika input 'makan nasi.' dan kamus punya 'makan = makan', 'nasi = nasi'
                    - Maka output: 'makan nasi' (gunakan dari kamus, abaikan titik)
                    - Jika input 'lagi makan!' dan kamus punya 'makan = makan' tapi tidak ada 'lagi'
                    - Maka output: 'sedang makan' (kombinasi kamus + pengetahuan umum)

                    Teks untuk diterjemahkan: \"{$text}\"
                    
                    Terjemahan (HANYA hasil, tanpa penjelasan):";
            } else {
                $prompt = "
                    ATURAN MUTLAK: GUNAKAN KAMUS DATABASE INI SEBAGAI PRIORITAS UTAMA!

                    KAMUS BAHASA INDONESIA KE MELAYU BELITUNG (WAJIB DIPAKAI):
                    {$context}

                    INSTRUKSI KETAT:
                    1. CEK SETIAP KATA dalam kamus di atas TERLEBIH DAHULU
                    2. JIKA kata ADA dalam kamus, WAJIB gunakan terjemahan persis dari kamus
                    3. JIKA kata TIDAK ADA dalam kamus, gunakan pengetahuan umum bahasa Melayu
                    4. Untuk kalimat utuh, prioritaskan makna dari kamus tapi sesuaikan konteks
                    5. Jangan ubah kata yang sudah ada terjemahannya di kamus
                    6. Hasil harus natural tapi tetap akurat sesuai kamus
                    7. ABAIKAN tanda baca (titik, koma, dsb) saat mencari di kamus

                    CONTOH PENGGUNAAN:
                    - Jika input 'makan nasi.' dan kamus punya 'makan = makan', 'nasi = nasi'
                    - Maka output: 'makan nasi' (gunakan dari kamus, abaikan titik)
                    - Jika input 'sedang makan!' dan kamus punya 'makan = makan' tapi tidak ada 'sedang'
                    - Maka output: 'lagi makan' (kombinasi kamus + pengetahuan umum)

                    Teks untuk diterjemahkan: \"{$text}\"
                    
                    Terjemahan (HANYA hasil, tanpa penjelasan):";
            }

            $response = Http::timeout(30)->post($this->apiUrl . '?key=' . $this->apiKey, [
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
                
                // Clean up common AI responses
                $translation = trim($translation);
                $translation = preg_replace('/^(Terjemahan:|Translation:)/i', '', $translation);
                $translation = trim($translation);
                
                return [
                    'success' => true,
                    'input' => $text,
                    'translation' => $translation,
                    'direction' => $direction,
                    'method' => 'ai_with_optimized_context',
                    'confidence' => 'medium',
                    'context_words_used' => substr_count($context, "\n") - 1,
                    'context_preview' => substr($context, 0, 200) . '...'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'API Error: ' . $response->body()
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * IMPROVED: Optimized Context - Fokus pada kata yang relevan
     */
    private function createOptimizedContext($text, $direction = 'melayu_to_indonesia')
    {
        $cacheKey = "optimized_context_v4_" . md5($text . $direction);
        
        return Cache::remember($cacheKey, 3600, function() use ($text, $direction) {
            // 1. Cari kata yang sangat relevan (exact dan partial match)
            $highRelevantWords = $this->findHighRelevantWords($text, $direction);
            
            // 2. Cari kata yang serupa secara semantik
            $similarWords = $this->findSimilarWords($text, $direction);
            
            // 3. Tambahkan beberapa kata umum untuk konteks (tapi sedikit)
            $commonWords = $this->getCommonWords(50); // Kurangi dari 200 ke 50
            
            // 4. Prioritaskan relevance tinggi, lalu similar, baru common
            $allWords = $highRelevantWords
                ->merge($similarWords)
                ->merge($commonWords)
                ->unique('bahasa_melayu')
                ->take(150); // Kurangi total dari 300 ke 150
            
            if ($allWords->isEmpty()) {
                return "Tidak ada data kamus tersedia.";
            }

            if ($direction == 'melayu_to_indonesia') {
                $context = "KAMUS PRIORITAS BAHASA MELAYU BELITUNG KE INDONESIA:\n";
                foreach ($allWords as $item) {
                    $context .= "{$item->bahasa_melayu} → {$item->bahasa_indonesia}\n";
                }
            } else {
                $context = "KAMUS PRIORITAS BAHASA INDONESIA KE MELAYU BELITUNG:\n";
                foreach ($allWords as $item) {
                    $context .= "{$item->bahasa_indonesia} → {$item->bahasa_melayu}\n";
                }
            }
            
            return $context;
        });
    }

    /**
     * IMPROVED: Cari kata dengan relevansi tinggi - dengan text cleaning
     */
    private function findHighRelevantWords($text, $direction = 'melayu_to_indonesia', $limit = 50)
    {
        // Clean text dulu sebelum split
        $cleanedText = $this->cleanText($text);
        $words = explode(' ', $cleanedText);
        $relevantData = collect();
        
        foreach ($words as $word) {
            $cleanWord = $this->cleanWord($word);
            if (strlen($cleanWord) < 2) continue;
            
            if ($direction == 'melayu_to_indonesia') {
                // Prioritas 1: Exact match
                $exactMatches = Kamus::where('status', 1)
                    ->whereRaw('LOWER(bahasa_melayu) = ?', [$cleanWord])
                    ->limit(5)
                    ->get(['bahasa_melayu', 'bahasa_indonesia']);
                
                // Prioritas 2: Starts with
                $startsWithMatches = Kamus::where('status', 1)
                    ->whereRaw('LOWER(bahasa_melayu) LIKE ?', [$cleanWord . '%'])
                    ->limit(10)
                    ->get(['bahasa_melayu', 'bahasa_indonesia']);
                
                // Prioritas 3: Contains
                $containsMatches = Kamus::where('status', 1)
                    ->whereRaw('LOWER(bahasa_melayu) LIKE ?', ['%' . $cleanWord . '%'])
                    ->where('bahasa_melayu', '!=', $cleanWord) // Exclude exact matches
                    ->limit(10)
                    ->get(['bahasa_melayu', 'bahasa_indonesia']);
                
                $relevantData = $relevantData->merge($exactMatches)
                    ->merge($startsWithMatches)
                    ->merge($containsMatches);
            } else {
                // Same logic for indonesia to melayu
                $exactMatches = Kamus::where('status', 1)
                    ->whereRaw('LOWER(bahasa_indonesia) = ?', [$cleanWord])
                    ->limit(5)
                    ->get(['bahasa_melayu', 'bahasa_indonesia']);
                
                $startsWithMatches = Kamus::where('status', 1)
                    ->whereRaw('LOWER(bahasa_indonesia) LIKE ?', [$cleanWord . '%'])
                    ->limit(10)
                    ->get(['bahasa_melayu', 'bahasa_indonesia']);
                
                $containsMatches = Kamus::where('status', 1)
                    ->whereRaw('LOWER(bahasa_indonesia) LIKE ?', ['%' . $cleanWord . '%'])
                    ->where('bahasa_indonesia', '!=', $cleanWord)
                    ->limit(10)
                    ->get(['bahasa_melayu', 'bahasa_indonesia']);
                
                $relevantData = $relevantData->merge($exactMatches)
                    ->merge($startsWithMatches)
                    ->merge($containsMatches);
            }
        }
        
        return $relevantData->unique('bahasa_melayu')->take($limit);
    }

    /**
     * NEW: Cari kata yang mirip secara semantik
     */
    private function findSimilarWords($text, $direction = 'melayu_to_indonesia', $limit = 30)
    {
        // Kata-kata yang sering muncul bersamaan atau memiliki root yang sama
        $cleanedText = $this->cleanText($text);
        $textLength = strlen($cleanedText);
        
        if ($textLength < 5) {
            return collect(); // Skip untuk teks sangat pendek
        }
        
        // Ambil kata berdasarkan panjang yang serupa
        $similarLengthWords = Kamus::where('status', 1)
            ->whereBetween(
                \DB::raw('CHAR_LENGTH(' . ($direction == 'melayu_to_indonesia' ? 'bahasa_melayu' : 'bahasa_indonesia') . ')'),
                [strlen($cleanedText) - 2, strlen($cleanedText) + 2]
            )
            ->inRandomOrder()
            ->limit($limit)
            ->get(['bahasa_melayu', 'bahasa_indonesia']);
        
        return $similarLengthWords;
    }

    /**
     * IMPROVED: Ambil kata umum yang sering digunakan (kurangi noise)
     */
    private function getCommonWords($count = 50)
    {
        return Kamus::where('status', 1)
            ->whereIn('bahasa_melayu', [
                // Kata-kata dasar yang sering digunakan
                'aku', 'kamu', 'dia', 'kita', 'mereka',
                'ini', 'itu', 'di', 'ke', 'dari',
                'dan', 'atau', 'tapi', 'kalau', 'karena',
                'sudah', 'belum', 'akan', 'sedang', 'telah',
                'bisa', 'tidak', 'ya', 'baik', 'bagus'
            ])
            ->get(['bahasa_melayu', 'bahasa_indonesia'])
            ->merge(
                Kamus::where('status', 1)
                    ->whereNotIn('bahasa_melayu', [
                        'aku', 'kamu', 'dia', 'kita', 'mereka',
                        'ini', 'itu', 'di', 'ke', 'dari',
                        'dan', 'atau', 'tapi', 'kalau', 'karena',
                        'sudah', 'belum', 'akan', 'sedang', 'telah',
                        'bisa', 'tidak', 'ya', 'baik', 'bagus'
                    ])
                    ->inRandomOrder()
                    ->limit($count - 25) // Sisanya random
                    ->get(['bahasa_melayu', 'bahasa_indonesia'])
            );
    }

    /**
     * Statistik database untuk monitoring
     */
    public function getDatabaseStats()
    {
        return [
            'total_words' => Kamus::where('status', 1)->count(),
            'melayu_sample' => Kamus::where('status', 1)->limit(5)->pluck('bahasa_melayu')->toArray(),
            'indonesia_sample' => Kamus::where('status', 1)->limit(5)->pluck('bahasa_indonesia')->toArray(),
        ];
    }

    /**
     * IMPROVED: Test method dengan detail yang lebih baik
     */
    public function testTranslateDetails($text, $direction = 'melayu_to_indonesia')
    {
        $result = [];
        
        // Test text cleaning
        $result['original_text'] = $text;
        $result['cleaned_text'] = $this->cleanText($text);
        $result['cleaned_words'] = explode(' ', $this->cleanText($text));
        
        // Test context creation
        $context = $this->createOptimizedContext($text, $direction);
        $result['context_words'] = substr_count($context, "\n") - 1;
        $result['context_preview'] = substr($context, 0, 500) . '...';
        $result['context_full'] = strlen($context) > 1000 ? 'Too long to display' : $context;
        
        // Test high relevant words
        $highRelevant = $this->findHighRelevantWords($text, $direction);
        $result['high_relevant_count'] = $highRelevant->count();
        $result['high_relevant_sample'] = $highRelevant->take(5)->map(function($item) {
            return $item->bahasa_melayu . ' → ' . $item->bahasa_indonesia;
        })->toArray();
        
        // Final translation
        $translation = $this->translateDirectlyWithAI($text, $direction);
        $result['final_result'] = $translation;
        
        return $result;
    }

    // Backward compatibility methods
    public function loadKamusData($limit = 100)
    {
        try {
            $kamusData = Kamus::where('status', 1)
                ->limit($limit)
                ->get(['bahasa_melayu', 'bahasa_indonesia'])
                ->toArray();

            return $kamusData;

        } catch (\Exception $e) {
            Log::error('Error loading kamus data: ' . $e->getMessage());
            return [];
        }
    }

    public function testKamusData()
    {
        $data = $this->loadKamusData(10);
        
        return [
            'total_loaded' => count($data),
            'sample_data' => $data
        ];
    }

    public function translateToEnglish($text)
    {
        $prompt = "Translate this Indonesian text to English. Only return the translation, no explanation: \"{$text}\"";
        
        $response = Http::timeout(30)->post($this->apiUrl . '?key=' . $this->apiKey, [
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
            
            return [
                'success' => true,
                'translation' => trim($translation)
            ];
        }
        
        return ['success' => false, 'error' => 'Translation failed'];
    }

}