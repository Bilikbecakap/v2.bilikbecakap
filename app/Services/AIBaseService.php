<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\DatasetTranslate;

abstract class AIBaseService
{
    // -------------------------------------------------------------------------
    // Text helpers
    // -------------------------------------------------------------------------

    protected function cleanText(string $text): string
    {
        $cleaned = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        return trim(strtolower($cleaned));
    }

    protected function cleanWord(string $word): string
    {
        $cleaned = preg_replace('/^[^\p{L}\p{N}]+|[^\p{L}\p{N}]+$/u', '', $word);
        return trim(strtolower($cleaned));
    }

    protected function postProcessAIResult(string $text, string $originalText): string
    {
        $text = trim($text);
        $text = preg_replace('/^(Terjemahan:|Translation:)/i', '', $text);
        $text = trim($text);
        return $this->matchOriginalCapitalizationPattern($text, $originalText);
    }

    protected function matchOriginalCapitalizationPattern(string $translation, string $originalText): string
    {
        if (preg_match('/^\p{Lu}/u', $originalText)) {
            $translation = ucfirst($translation);
        }
        $translation = preg_replace_callback('/([.!?])\s+(\p{Ll})/u', function ($matches) {
            return $matches[1] . ' ' . strtoupper($matches[2]);
        }, $translation);
        return $translation;
    }

    // -------------------------------------------------------------------------
    // Kamus / Database helpers
    // -------------------------------------------------------------------------

    protected function createOptimizedContext(string $text, string $direction = 'belitung_to_indonesia'): string
    {
        $cacheKey = "optimized_context_v5_" . md5($text . $direction);
        return Cache::remember($cacheKey, 3600, function () use ($text, $direction) {
            $highRelevantWords = $this->findHighRelevantWords($text, $direction);
            $similarWords      = $this->findSimilarWords($text, $direction);
            $commonWords       = $this->getCommonWords(50);

            $allWords = $highRelevantWords->merge($similarWords)->merge($commonWords)
                ->unique('bahasa_belitung')->take(80);

            if ($allWords->isEmpty()) {
                return "Tidak ada data kamus tersedia.";
            }

            if ($direction === 'belitung_to_indonesia') {
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

    protected function findHighRelevantWords(string $text, string $direction = 'belitung_to_indonesia', int $limit = 50)
    {
        $cleanedText   = $this->cleanText($text);
        $words         = explode(' ', $cleanedText);
        $relevantData  = collect();

        foreach ($words as $word) {
            $cleanWord = $this->cleanWord($word);
            if (strlen($cleanWord) < 2) continue;

            if ($direction === 'belitung_to_indonesia') {
                $exactMatches    = DatasetTranslate::whereRaw('LOWER(bahasa_belitung) = ?', [$cleanWord])->limit(5)->get(['bahasa_belitung', 'bahasa_indonesia']);
                $startsWithMatches = DatasetTranslate::whereRaw('LOWER(bahasa_belitung) LIKE ?', [$cleanWord . '%'])->limit(10)->get(['bahasa_belitung', 'bahasa_indonesia']);
                $containsMatches = DatasetTranslate::whereRaw('LOWER(bahasa_belitung) LIKE ?', ['%' . $cleanWord . '%'])->where('bahasa_belitung', '!=', $cleanWord)->limit(10)->get(['bahasa_belitung', 'bahasa_indonesia']);
            } else {
                $exactMatches    = DatasetTranslate::whereRaw('LOWER(bahasa_indonesia) = ?', [$cleanWord])->limit(5)->get(['bahasa_belitung', 'bahasa_indonesia']);
                $startsWithMatches = DatasetTranslate::whereRaw('LOWER(bahasa_indonesia) LIKE ?', [$cleanWord . '%'])->limit(10)->get(['bahasa_belitung', 'bahasa_indonesia']);
                $containsMatches = DatasetTranslate::whereRaw('LOWER(bahasa_indonesia) LIKE ?', ['%' . $cleanWord . '%'])->where('bahasa_indonesia', '!=', $cleanWord)->limit(10)->get(['bahasa_belitung', 'bahasa_indonesia']);
            }
            $relevantData = $relevantData->merge($exactMatches)->merge($startsWithMatches)->merge($containsMatches);
        }

        return $relevantData->unique('bahasa_belitung')->take($limit);
    }

    protected function findSimilarWords(string $text, string $direction = 'belitung_to_indonesia', int $limit = 30)
    {
        $cleanedText = $this->cleanText($text);
        if (strlen($cleanedText) < 5) return collect();

        $col = $direction === 'belitung_to_indonesia' ? 'bahasa_belitung' : 'bahasa_indonesia';
        return DatasetTranslate::whereBetween(
            \DB::raw('CHAR_LENGTH(' . $col . ')'),
            [strlen($cleanedText) - 2, strlen($cleanedText) + 2]
        )->inRandomOrder()->limit($limit)->get(['bahasa_belitung', 'bahasa_indonesia']);
    }

    protected function getCommonWords(int $count = 50)
    {
        $common = ['aku', 'kamu', 'dia', 'kita', 'mereka', 'ini', 'itu', 'di', 'ke', 'dari', 'dan', 'atau', 'tapi', 'kalau', 'karena', 'sudah', 'belum', 'akan', 'sedang', 'telah', 'bisa', 'tidak', 'ya', 'baik', 'bagus'];
        return DatasetTranslate::whereIn('bahasa_belitung', $common)->get(['bahasa_belitung', 'bahasa_indonesia'])
            ->merge(
                DatasetTranslate::whereNotIn('bahasa_belitung', $common)->inRandomOrder()->limit($count - 25)->get(['bahasa_belitung', 'bahasa_indonesia'])
            );
    }

    // -------------------------------------------------------------------------
    // Website context untuk chatbot
    // -------------------------------------------------------------------------

    protected function getWebsiteContext(string $contextType = 'general'): string
    {
        return Cache::remember('website_context_full', 3600, fn() => $this->getFullContext());
    }

    protected function getFullContext(): string
    {
        $totalWords = DatasetTranslate::count();

        return "
            NAMA WEBSITE: Bilikbecakap (bilikbecakap.com)
            TUJUAN: Platform Pelestarian Budaya dan Bahasa Melayu Belitung

            TENTANG BILIKBECAKAP:
            Bilikbecakap lahir dari keresahan masyarakat Belitung Timur atas belum adanya arsip bahasa daerah Melayu Belitung yang dikhawatirkan akan hilang. Melalui fitur seperti penerjemah, konten informasi, serta video dan modul pembelajaran, Bilikbecakap berupaya melestarikan dan mengenalkan bahasa serta budaya lokal secara digital. Inisiatif ini berawal dari program Kemah Budaya Kaum Muda (KBKM) 2023 dan kini dikelola oleh Yayasan Basamudera Teknologi Indonesia.
            Pernah diperkenalkan di acara Kazan Digital Week, Russia dan Forum Internasional YSEALI.

            DIDUKUNG OLEH: Kementerian Kebudayaan, Pemerintah Belitung Timur, Komunitas lokal Belitung
            LOKASI: Belitung Timur, Indonesia | EMAIL: kbkmsenyubuk@gmail.com

            FITUR UTAMA:
            1. KAMUS DIGITAL - Database {$totalWords} kata Melayu Belitung, audio pronunciation, definisi, filter & sorting
            2. PENERJEMAH - Indonesia ↔ Melayu Belitung ↔ English, berbasis AI + database lokal
            3. PEMBELAJARAN - Modul interaktif, quiz, topik: Kosakata Dasar, Frasa Umum, Budaya & Tradisi, Sejarah Belitung
            4. GALERI - Dokumentasi budaya dan tradisi Belitung
            5. ARTIKEL - Tulisan dan berita tentang budaya dan bahasa Belitung

            FITUR SPESIAL: Support 3 bahasa (Indonesia, English, Melayu Belitung), AI-powered translator, audio pronunciation
        ";
    }

    protected function getGeneralContext(): string
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

            LOKASI: Belitung Timur, Indonesia
            EMAIL: kbkmsenyubuk@gmail.com

            FITUR SPESIAL:
            - Support 3 bahasa: Indonesia, English, Melayu Belitung
            - Database lebih dari 1000 kata
            - AI-powered translator
            - Interactive learning modules
            - Audio pronunciation
        ";
    }

    protected function getKamusContext(): string
    {
        $totalWords  = DatasetTranslate::count();
        $sampleWords = DatasetTranslate::limit(20)->get();

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

    protected function getPenerjemahContext(): string
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
        ";
    }

    protected function getBelajarContext(): string
    {
        return "
            MODUL PEMBELAJARAN BILIKBECAKAP

            FITUR:
            - Modul pembelajaran interaktif
            - Quiz untuk menguji pemahaman
            - Topik budaya Belitung
            - Peningkatan level kesulitan

            TOPIK TERSEDIA:
            - Kosakata Dasar
            - Frasa Umum
            - Budaya & Tradisi
            - Sejarah Belitung
        ";
    }

    protected function getTentangContext(): string
    {
        return "
            TENTANG BILIKBECAKAP

            Bilikbecakap lahir dari keresahan masyarakat Belitung Timur atas belum adanya arsip bahasa daerah Melayu Belitung yang dikhawatirkan akan hilang. Melalui fitur seperti penerjemah, konten informasi, serta video dan modul pembelajaran, Bilikbecakap berupaya melestarikan dan mengenalkan bahasa serta budaya lokal secara digital. Inisiatif ini berawal dari program Kemah Budaya Kaum Muda (KBKM) 2023 dan kini dikelola oleh Yayasan Basamudera Indonesia.

            DIDUKUNG OLEH:
            - Kementerian Kebudayaan
            - Pemerintah Belitung Timur
            - Komunitas lokal Belitung
        ";
    }

    // -------------------------------------------------------------------------
    // Public DB-only methods (tidak perlu AI)
    // -------------------------------------------------------------------------

    public function getDatabaseStats(): array
    {
        return [
            'total_words'    => DatasetTranslate::count(),
            'belitung_sample' => DatasetTranslate::limit(5)->pluck('bahasa_belitung')->toArray(),
            'indonesia_sample' => DatasetTranslate::limit(5)->pluck('bahasa_indonesia')->toArray(),
        ];
    }

    public function loadKamusData(int $limit = 100): array
    {
        try {
            return DatasetTranslate::limit($limit)->get(['bahasa_belitung', 'bahasa_indonesia'])->toArray();
        } catch (\Exception $e) {
            Log::error('Error loading dataset: ' . $e->getMessage());
            return [];
        }
    }

    public function testKamusData(): array
    {
        $data = $this->loadKamusData(10);
        return ['total_loaded' => count($data), 'sample_data' => $data];
    }

    // -------------------------------------------------------------------------
    // Prompt builders (re-used by concrete classes)
    // -------------------------------------------------------------------------

    protected function buildTranslatePrompt(string $text, string $direction): string
    {
        $context = $this->createOptimizedContext($text, $direction);

        if ($direction === 'belitung_to_indonesia') {
            return "
                ATURAN MUTLAK: GUNAKAN KAMUS DATABASE INI SEBAGAI PRIORITAS UTAMA!

                KAMUS BAHASA MELAYU BELITUNG KE INDONESIA (WAJIB DIPAKAI):
                {$context}

                INSTRUKSI KETAT:
                1. CEK SETIAP KATA dalam kamus di atas TERLEBIH DAHULU
                2. JIKA kata ADA dalam kamus, WAJIB gunakan terjemahan persis dari kamus
                3. JIKA kata TIDAK ADA dalam kamus, gunakan pengetahuan umum bahasa Melayu Belitung
                4. Hasil harus natural tapi tetap akurat sesuai kamus
                5. ABAIKAN tanda baca (titik, koma, dsb) saat mencari di kamus
                6. PERTAHANKAN format kapitalisasi standar bahasa Indonesia

                Teks untuk diterjemahkan: \"{$text}\"

                Terjemahan (HANYA hasil, tanpa penjelasan, dengan kapitalisasi yang benar):";
        }

        return "
            ATURAN MUTLAK: GUNAKAN KAMUS DATABASE INI SEBAGAI PRIORITAS UTAMA!

            KAMUS BAHASA INDONESIA KE MELAYU BELITUNG (WAJIB DIPAKAI):
            {$context}

            INSTRUKSI KETAT:
            1. CEK SETIAP KATA dalam kamus di atas TERLEBIH DAHULU
            2. JIKA kata ADA dalam kamus, WAJIB gunakan terjemahan persis dari kamus
            3. JIKA kata TIDAK ADA dalam kamus, gunakan pengetahuan umum bahasa Melayu Belitung
            4. Hasil harus natural tapi tetap akurat sesuai kamus
            5. ABAIKAN tanda baca (titik, koma, dsb) saat mencari di kamus
            6. PERTAHANKAN format kapitalisasi standar bahasa Belitung

            Teks untuk diterjemahkan: \"{$text}\"

            Terjemahan (HANYA hasil, tanpa penjelasan, dengan kapitalisasi yang benar):";
    }

    protected function buildChatPrompt(string $userMessage, string $contextType, string $historyText = ''): string
    {
        $context = $this->getWebsiteContext($contextType);
        $historySection = $historyText ? "RIWAYAT PERCAKAPAN (5 pesan terakhir):\n{$historyText}\n" : '';

        return "
            Anda adalah asisten AI resmi untuk website Bilikbecakap - Platform Pelestarian Budaya dan Bahasa Melayu Belitung.

            PERAN ANDA:
            - Nama: Asisten Bilikbecakap
            - Fokus utama: Bahasa Melayu Belitung dan pelestarian budaya Belitung
            - Bahasa: Gunakan Bahasa Indonesia yang ramah, hangat, dan profesional
            - Jika user menyapa dalam Bahasa Melayu Belitung, balas dengan sapaan yang sama sebelum menjawab

            INFORMASI WEBSITE:
            {$context}

            {$historySection}
            INSTRUKSI:
            1. Jawab pertanyaan user berdasarkan informasi website di atas dan riwayat percakapan
            2. Jika pertanyaan tidak ada dalam informasi website, katakan dengan jujur bahwa Anda tidak memiliki informasi tersebut
            3. Pertahankan fokus pada Bahasa Melayu Belitung dan pelestarian budaya Belitung
            4. Pertahankan konsistensi dengan jawaban sebelumnya dalam riwayat percakapan
            5. Jika user bertanya tentang fitur website, jelaskan dengan detail dan jelas
            6. Selalu tawarkan bantuan lebih lanjut di akhir setiap jawaban
            7. Jangan menjawab pertanyaan yang tidak berkaitan dengan Bilikbecakap atau Bahasa/Budaya Belitung

            Pertanyaan User: \"{$userMessage}\"

            Jawaban (natural, informatif, dan tetap fokus pada Bilikbecakap):";
    }
}
