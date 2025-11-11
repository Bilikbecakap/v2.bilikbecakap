<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\DatasetTranslate;

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
     * NEW: Post-process untuk memastikan kapitalisasi yang benar pada hasil AI
     */
    private function postProcessAIResult($text, $originalText)
    {
        // Clean up common AI responses patterns
        $text = trim($text);
        $text = preg_replace('/^(Terjemahan:|Translation:)/i', '', $text);
        $text = trim($text);
        
        // Apply proper capitalization based on original text pattern
        $text = $this->matchOriginalCapitalizationPattern($text, $originalText);
        
        return $text;
    }

    /**
     * NEW: Match capitalization pattern dari original text
     */
    private function matchOriginalCapitalizationPattern($translation, $originalText)
    {
        // Capitalize first letter jika original text dimulai dengan huruf kapital
        if (preg_match('/^\p{Lu}/u', $originalText)) {
            $translation = ucfirst($translation);
        }
        
        // Capitalize after sentence-ending punctuation followed by space
        $translation = preg_replace_callback('/([.!?])\s+(\p{Ll})/u', function($matches) {
            return $matches[1] . ' ' . strtoupper($matches[2]);
        }, $translation);
        
        return $translation;
    }

    /**
     * Test koneksi ke Gemini API
     */
    public function testConnection()
    {
        try {
            $response = Http::timeout(10)->withOptions(['force_ip_resolve' => 'v4'])->post($this->apiUrl . '?key=' . $this->apiKey, [
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
     * IMPROVED: Translate dengan AI + Database Context yang Lebih Optimal dengan Proper Capitalization
     */
    public function translateDirectlyWithAI($text, $direction = 'belitung_to_indonesia')  
    {
        try {
            $context = $this->createOptimizedContext($text, $direction);
            
            if ($direction == 'belitung_to_indonesia') { 
                $prompt = "
                    ATURAN MUTLAK: GUNAKAN KAMUS DATABASE INI SEBAGAI PRIORITAS UTAMA!

                    KAMUS BAHASA MELAYU BELITUNG KE INDONESIA (WAJIB DIPAKAI): 
                    {$context}

                    INSTRUKSI KETAT:
                    1. CEK SETIAP KATA dalam kamus di atas TERLEBIH DAHULU
                    2. JIKA kata ADA dalam kamus, WAJIB gunakan terjemahan persis dari kamus
                    3. JIKA kata TIDAK ADA dalam kamus, gunakan pengetahuan umum bahasa Melayu Belitung 
                    4. Untuk kalimat utuh, prioritaskan makna dari kamus tapi sesuaikan konteks
                    5. Jangan ubah kata yang sudah ada terjemahannya di kamus
                    6. Hasil harus natural tapi tetap akurat sesuai kamus
                    7. ABAIKAN tanda baca (titik, koma, dsb) saat mencari di kamus
                    8. PERTAHANKAN format kapitalisasi yang sesuai standar bahasa Indonesia:
                       - Huruf kapital di awal kalimat
                       - Huruf kapital setelah tanda titik, tanda seru, tanda tanya
                       - Nama diri tetap kapital
                       - Kata dari kamus gunakan lowercase kecuali di awal kalimat

                    CONTOH PENGGUNAAN:
                    - Input: 'Ibu pergi ke pasar.' → Output: 'Ibu pergi ke pasar.' (kapitalisasi sesuai posisi)
                    - Input: 'saya makan nasi.' → Output: 'Saya makan nasi.' (kapitalisasi awal kalimat)
                    - Input: 'lagi makan! dia datang.' → Output: 'Sedang makan! Dia datang.' (kapitalisasi setelah tanda seru)

                    Teks untuk diterjemahkan: \"{$text}\"
                    
                    Terjemahan (HANYA hasil, tanpa penjelasan, dengan kapitalisasi yang benar):";
            } else {
                $prompt = "
                    ATURAN MUTLAK: GUNAKAN KAMUS DATABASE INI SEBAGAI PRIORITAS UTAMA!

                    KAMUS BAHASA INDONESIA KE MELAYU BELITUNG (WAJIB DIPAKAI): 
                    {$context}

                    INSTRUKSI KETAT:
                    1. CEK SETIAP KATA dalam kamus di atas TERLEBIH DAHULU
                    2. JIKA kata ADA dalam kamus, WAJIB gunakan terjemahan persis dari kamus
                    3. JIKA kata TIDAK ADA dalam kamus, gunakan pengetahuan umum bahasa Melayu Belitung  
                    4. Untuk kalimat utuh, prioritaskan makna dari kamus tapi sesuaikan konteks
                    5. Jangan ubah kata yang sudah ada terjemahannya di kamus
                    6. Hasil harus natural tapi tetap akurat sesuai kamus
                    7. ABAIKAN tanda baca (titik, koma, dsb) saat mencari di kamus
                    8. PERTAHANKAN format kapitalisasi yang sesuai standar bahasa Belitung:  
                       - Huruf kapital di awal kalimat
                       - Huruf kapital setelah tanda titik, tanda seru, tanda tanya
                       - Nama diri tetap kapital
                       - Kata dari kamus gunakan lowercase kecuali di awal kalimat

                    CONTOH PENGGUNAAN:
                    - Input: 'Ibu pergi ke pasar.' → Output: 'Uma pergi ke pasar.' (kapitalisasi sesuai posisi)
                    - Input: 'saya makan nasi.' → Output: 'Aku makan nasi.' (kapitalisasi awal kalimat)
                    - Input: 'sedang makan! dia datang.' → Output: 'Lagi makan! Dia datang.' (kapitalisasi setelah tanda seru)

                    Teks untuk diterjemahkan: \"{$text}\"
                    
                    Terjemahan (HANYA hasil, tanpa penjelasan, dengan kapitalisasi yang benar):";
            }

            $response = Http::timeout(30)->withOptions(['force_ip_resolve' => 'v4'])->post($this->apiUrl . '?key=' . $this->apiKey, [
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
     * IMPROVED: Optimized Context - Fokus pada kata yang relevan dengan consistent formatting
     */
    private function createOptimizedContext($text, $direction = 'belitung_to_indonesia')  
    {
        $cacheKey = "optimized_context_v5_" . md5($text . $direction);
        
        return Cache::remember($cacheKey, 3600, function() use ($text, $direction) {
            $highRelevantWords = $this->findHighRelevantWords($text, $direction);
            $similarWords = $this->findSimilarWords($text, $direction);
            $commonWords = $this->getCommonWords(50);
            
            $allWords = $highRelevantWords
                ->merge($similarWords)
                ->merge($commonWords)
                ->unique('bahasa_belitung')
                ->take(150);
            
            if ($allWords->isEmpty()) {
                return "Tidak ada data kamus tersedia.";
            }

            if ($direction == 'belitung_to_indonesia') {  
                $context = "KAMUS PRIORITAS BAHASA MELAYU BELITUNG KE INDONESIA:\n";
                foreach ($allWords as $item) {
                    $belitung = strtolower(trim($item->bahasa_belitung)); 
                    $indonesia = strtolower(trim($item->bahasa_indonesia));
                    $context .= "{$belitung} → {$indonesia}\n";
                }
            } else {
                $context = "KAMUS PRIORITAS BAHASA INDONESIA KE MELAYU BELITUNG:\n"; 
                foreach ($allWords as $item) {
                    $indonesia = strtolower(trim($item->bahasa_indonesia));
                    $belitung = strtolower(trim($item->bahasa_belitung));  
                    $context .= "{$indonesia} → {$belitung}\n";
                }
            }
            
            return $context;
        });
    }

    /**
     * IMPROVED: Cari kata dengan relevansi tinggi - dengan text cleaning
     */
    private function findHighRelevantWords($text, $direction = 'belitung_to_indonesia', $limit = 50) 
    {
        $cleanedText = $this->cleanText($text);
        $words = explode(' ', $cleanedText);
        $relevantData = collect();
        
        foreach ($words as $word) {
            $cleanWord = $this->cleanWord($word);
            if (strlen($cleanWord) < 2) continue;
            
            if ($direction == 'belitung_to_indonesia') {  
                // Prioritas 1: Exact match
                $exactMatches = DatasetTranslate::whereRaw('LOWER(bahasa_belitung) = ?', [$cleanWord])  
                    ->limit(5)
                    ->get(['bahasa_belitung', 'bahasa_indonesia']);  
                
                // Prioritas 2: Starts with
                $startsWithMatches = DatasetTranslate::whereRaw('LOWER(bahasa_belitung) LIKE ?', [$cleanWord . '%'])  
                    ->limit(10)
                    ->get(['bahasa_belitung', 'bahasa_indonesia']);  
                
                // Prioritas 3: Contains
                $containsMatches = DatasetTranslate::whereRaw('LOWER(bahasa_belitung) LIKE ?', ['%' . $cleanWord . '%'])  
                    ->where('bahasa_belitung', '!=', $cleanWord)  
                    ->limit(10)
                    ->get(['bahasa_belitung', 'bahasa_indonesia']);  
                
                $relevantData = $relevantData->merge($exactMatches)
                    ->merge($startsWithMatches)
                    ->merge($containsMatches);
            } else {
                $exactMatches = DatasetTranslate::whereRaw('LOWER(bahasa_indonesia) = ?', [$cleanWord])
                    ->limit(5)
                    ->get(['bahasa_belitung', 'bahasa_indonesia']); 
                
                $startsWithMatches = DatasetTranslate::whereRaw('LOWER(bahasa_indonesia) LIKE ?', [$cleanWord . '%'])
                    ->limit(10)
                    ->get(['bahasa_belitung', 'bahasa_indonesia']); 
                
                $containsMatches = DatasetTranslate::whereRaw('LOWER(bahasa_indonesia) LIKE ?', ['%' . $cleanWord . '%'])
                    ->where('bahasa_indonesia', '!=', $cleanWord)
                    ->limit(10)
                    ->get(['bahasa_belitung', 'bahasa_indonesia']);  
                
                $relevantData = $relevantData->merge($exactMatches)
                    ->merge($startsWithMatches)
                    ->merge($containsMatches);
            }
        }
        
        return $relevantData->unique('bahasa_belitung')->take($limit); 
    }

    /**
     * NEW: Cari kata yang mirip secara semantik
     */
    private function findSimilarWords($text, $direction = 'belitung_to_indonesia', $limit = 30)  
    {
        $cleanedText = $this->cleanText($text);
        $textLength = strlen($cleanedText);
        
        if ($textLength < 5) {
            return collect();
        }
        
        $similarLengthWords = DatasetTranslate::whereBetween(
                \DB::raw('CHAR_LENGTH(' . ($direction == 'belitung_to_indonesia' ? 'bahasa_belitung' : 'bahasa_indonesia') . ')'),  
                [strlen($cleanedText) - 2, strlen($cleanedText) + 2]
            )
            ->inRandomOrder()
            ->limit($limit)
            ->get(['bahasa_belitung', 'bahasa_indonesia']);  
        
        return $similarLengthWords;
    }

    /**
     * IMPROVED: Ambil kata umum yang sering digunakan
     */
    private function getCommonWords($count = 50)
    {
        return DatasetTranslate::whereIn('bahasa_belitung', [ 
                'aku', 'kamu', 'dia', 'kita', 'mereka',
                'ini', 'itu', 'di', 'ke', 'dari',
                'dan', 'atau', 'tapi', 'kalau', 'karena',
                'sudah', 'belum', 'akan', 'sedang', 'telah',
                'bisa', 'tidak', 'ya', 'baik', 'bagus'
            ])
            ->get(['bahasa_belitung', 'bahasa_indonesia'])  
            ->merge(
                DatasetTranslate::whereNotIn('bahasa_belitung', [  
                        'aku', 'kamu', 'dia', 'kita', 'mereka',
                        'ini', 'itu', 'di', 'ke', 'dari',
                        'dan', 'atau', 'tapi', 'kalau', 'karena',
                        'sudah', 'belum', 'akan', 'sedang', 'telah',
                        'bisa', 'tidak', 'ya', 'baik', 'bagus'
                    ])
                    ->inRandomOrder()
                    ->limit($count - 25)
                    ->get(['bahasa_belitung', 'bahasa_indonesia']) 
            );
    }

    /**
     * Statistik database untuk monitoring
     */
    public function getDatabaseStats()
    {
        return [
            'total_words' => DatasetTranslate::count(),
            'belitung_sample' => DatasetTranslate::limit(5)->pluck('bahasa_belitung')->toArray(),  
            'indonesia_sample' => DatasetTranslate::limit(5)->pluck('bahasa_indonesia')->toArray(),
        ];
    }

    /**
     * IMPROVED: Test method dengan detail yang lebih baik
     */
    public function testTranslateDetails($text, $direction = 'belitung_to_indonesia')  
    {
        $result = [];
        
        $result['original_text'] = $text;
        $result['cleaned_text'] = $this->cleanText($text);
        $result['cleaned_words'] = explode(' ', $this->cleanText($text));
        
        $context = $this->createOptimizedContext($text, $direction);
        $result['context_words'] = substr_count($context, "\n") - 1;
        $result['context_preview'] = substr($context, 0, 500) . '...';
        $result['context_full'] = strlen($context) > 10000 ? 'Too long to display' : $context;
        
        $highRelevant = $this->findHighRelevantWords($text, $direction);
        $result['high_relevant_count'] = $highRelevant->count();
        $result['high_relevant_sample'] = $highRelevant->take(5)->map(function($item) {
            return $item->bahasa_belitung . ' → ' . $item->bahasa_indonesia;  
        })->toArray();
        
        $translation = $this->translateDirectlyWithAI($text, $direction);
        $result['final_result'] = $translation;
        
        return $result;
    }

    public function loadKamusData($limit = 100)
    {
        try {
            $kamusData = DatasetTranslate::limit($limit)
                ->get(['bahasa_belitung', 'bahasa_indonesia'])  
                ->toArray();

            return $kamusData;

        } catch (\Exception $e) {
            Log::error('Error loading dataset data: ' . $e->getMessage());
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
        $prompt = "Translate this Indonesian text to English. Only return the translation, no explanation. Maintain proper capitalization: \"{$text}\"";
        
        $response = Http::timeout(30)->withOptions(['force_ip_resolve' => 'v4'])->post($this->apiUrl . '?key=' . $this->apiKey, [
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
            
            // Apply post-processing untuk proper capitalization
            $translation = $this->postProcessAIResult($translation, $text);
            
            return [
                'success' => true,
                'translation' => $translation
            ];
        }
        
        return ['success' => false, 'error' => 'Translation failed'];
    }

    /**
     * Chat Assistant dengan Context dari Website
     */
    public function chatWithContext($userMessage, $contextType = 'general')
    {
        try {
            $context = $this->getWebsiteContext($contextType);
            
            $prompt = "
                Anda adalah asisten AI untuk website Bilikbecakap - Platform Pelestarian Budaya dan Bahasa Melayu Belitung.
                
                INFORMASI WEBSITE:
                {$context}
                
                INSTRUKSI:
                1. Jawab pertanyaan user berdasarkan informasi website di atas
                2. Jika pertanyaan tidak ada dalam informasi website, katakan Anda tidak memiliki informasi tersebut
                3. Gunakan bahasa Indonesia yang ramah dan profesional
                4. Pertahankan fokus pada Bahasa Melayu Belitung dan pelestarian budaya
                5. Jika user bertanya tentang fitur, jelaskan dengan detail
                6. Selalu tawarkan bantuan lebih lanjut di akhir jawaban
                
                Pertanyaan User: \"{$userMessage}\"
                
                Jawaban (natural dan informatif):";

            $response = Http::timeout(30)->withOptions(['force_ip_resolve' => 'v4'])->post($this->apiUrl . '?key=' . $this->apiKey, [
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
                return [
                    'success' => false,
                    'error' => 'API Error: ' . $response->body()
                ];
            }

        } catch (\Exception $e) {
            Log::error('Chatbot Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Ambil Context dari Website
     */
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
                case 'general':
                default:
                    return $this->getGeneralContext();
            }
        });
    }

    /**
     * Context Umum Website
     */
    private function getGeneralContext()
    {
        return "
            NAMA WEBSITE: Bilikbecakap (bilikbecakap.com)
            TUJUAN: Platform Pelestarian Budaya dan Bahasa Melayu Belitung
            
            FITUR UTAMA:
            1. Kamus Digital - Database kosakata Melayu Belitung dengan audio
            2. Penerjemah - Terjemahkan Indonesia ↔ Melayu Belitung dan sebaliknya
            3. Pembelajaran - Modul pembelajaran dan quiz interaktif
            4. Galeri - Dokumentasi budaya Belitung
            5. Artikel - Tulisan, Artikel dan Berita tentang budaya dan bahasa Belitung
            
            LOKASI: Senyubuk, Belitung Timur, Indonesia
            EMAIL: kbkmsenyubuk@gmail.com
            
            FITUR SPESIAL:
            - Support 3 bahasa: Indonesia, English, Melayu Belitung
            - Database lebih dari 1000 kata
            - AI-powered translator
            - Interactive learning modules
            - Audio pronunciation
        ";
    }

    /**
     * Context Kamus
     */
    private function getKamusContext()
    {
        $totalWords = \App\Models\DatasetTranslate::count();
        $sampleWords = \App\Models\DatasetTranslate::limit(20)->get();
        
        $context = "
            KAMUS DIGITAL BILIKBECAKAP
            Total Kata: {$totalWords}
            
            FITUR KAMUS:
            - Pencarian cepat untuk kata Melayu Belitung atau Indonesia
            - Audio pronunciation untuk setiap kata
            - Keterangan/definisi untuk setiap kata
            - Filter dan sorting
            
            CONTOH KATA DALAM DATABASE:
        ";
        
        foreach ($sampleWords as $word) {
            $context .= "\n- {$word->bahasa_belitung} (Melayu Belitung) = {$word->bahasa_indonesia} (Indonesia)";
        }
        
        return $context;
    }

    /**
     * Context Penerjemah
     */
    private function getPenerjemahContext()
    {
        return "
            PENERJEMAH BILIKBECAKAP
            
            FITUR:
            - Terjemahkan Indonesia → Melayu Belitung
            - Terjemahkan Melayu Belitung → Indonesia
            - Powered by Artificial Intelligence + Database Lokal
            - Akurasi tinggi dengan konteks kamus lokal
            
            CARA KERJA:
            1. Input teks dalam salah satu bahasa
            2. AI mencari kata dalam database kamus
            3. Menerjemahkan dengan konteks yang tepat
            4. Hasil ditampilkan dengan kapitalisasi yang benar
            
            DUKUNGAN:
            - Kalimat panjang
            - Frasa kompleks
            - Pertahankan makna asli
        ";
    }

    /**
     * Context Pembelajaran
     */
    private function getBelajarContext()
    {
        return "
            MODUL PEMBELAJARAN BILIKBECAKAP
            
            FITUR:
            - Modul pembelajaran interaktif
            - Quiz untuk menguji pemahaman
            - Topik budaya Belitung
            - Peningkatan level kesulitan
            
            MANFAAT:
            - Belajar bahasa dengan cara yang fun
            - Pahami budaya Belitung lebih dalam
            - Tracking progress pembelajaran
            - Sertifikat untuk pengguna terdaftar
            
            TOPIK TERSEDIA:
            - Kosakata Dasar
            - Frasa Umum
            - Budaya & Tradisi
            - Sejarah Belitung
        ";
    }

    /**
     * Context Tentang
     */
    private function getTentangContext()
    {
        return "
            TENTANG BILIKBECAKAP
            
            Bilikbecakap lahir dari keresahan masyarakat Belitung Timur atas belum adanya arsip bahasa daerah Melayu Belitung yang dikhawatirkan akan hilang. Melalui fitur seperti penerjemah, konten informasi, serta video dan modul pembelajaran, Bilikbecakap berupaya melestarikan dan mengenalkan bahasa serta budaya lokal secara digital. Inisiatif ini berawal dari program Kemah Budaya Kaum Muda (KBKM) 2023 dan kini dikelola oleh Yayasan Basamudera Indonesia yang melanjutkan semangat kolaborasi, inovasi, dan pelestarian bahasa daerah di era modern.
            
            DIDUKUNG OLEH:
            - Kementerian Kebudayaan
            - Pemerintah Belitung Timur
            - Komunitas lokal Belitung
            
            PERJALANAN:
            - Dimulai dari kesadaran akan pentingnya pelestarian bahasa daerah
            - Terus berkembang dengan fitur-fitur baru
            - Dukungan dari komunitas dan pemerintah
            - Pernah mengenalkan bilikbecakap di acara Kazan Digital Week, Russia dan Forum Internasional YSEALI
        ";
    }

    /**
     * Chat dengan Conversation History (untuk continuous chat)
     */
    public function chatWithHistory($userMessage, $conversationHistory = [], $contextType = 'general')
    {
        try {
            $context = $this->getWebsiteContext($contextType);
            
            // Format conversation history
            $historyText = "";
            foreach ($conversationHistory as $msg) {
                $historyText .= "User: {$msg['user']}\n";
                $historyText .= "Assistant: {$msg['assistant']}\n\n";
            }
            
            $prompt = "
                Anda adalah asisten AI untuk website Bilikbecakap - Platform Pelestarian Bahasa dan Budaya Melayu Belitung.
                
                INFORMASI WEBSITE:
                {$context}
                
                RIWAYAT PERCAKAPAN:
                {$historyText}
                
                INSTRUKSI:
                1. Jawab pertanyaan user berdasarkan informasi website dan riwayat percakapan
                2. Pertahankan konsistensi dengan jawaban sebelumnya
                3. Gunakan bahasa Indonesia yang ramah dan profesional
                4. Jika user bertanya hal yang tidak ada di website, katakan dengan jujur
                5. Tawarkan bantuan atau pertanyaan follow-up
                
                Pertanyaan User Terbaru: \"{$userMessage}\"
                
                Jawaban (lanjutkan percakapan dengan natural):";

            $response = Http::timeout(30)->withOptions(['force_ip_resolve' => 'v4'])->post($this->apiUrl . '?key=' . $this->apiKey, [
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
                return [
                    'success' => false,
                    'error' => 'API Error'
                ];
            }

        } catch (\Exception $e) {
            Log::error('Chatbot Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

}