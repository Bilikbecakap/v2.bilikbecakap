<?php

namespace App\Console\Commands;

use App\Models\Kamus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportKamusCSV extends Command
{
    protected $signature = 'kamus:import
                            {file : Path ke file CSV/TSV}
                            {--user-id=1 : User ID untuk kolom created_by}
                            {--status=1 : Status data: 1=aktif, 2=tidak aktif, 3=menunggu}
                            {--delimiter= : Pemisah kolom — default otomatis deteksi tab/koma}
                            {--dry-run : Simulasi tanpa menyimpan ke database}';

    protected $description = 'Import data kamus dari file CSV/TSV';

    // Pemetaan singkatan pos ke label Indonesia
    private const POS_LABELS = [
        'a'    => 'Adjektiva',
        'adv'  => 'Adverbia',
        'n'    => 'Nomina',
        'num'  => 'Numeralia',
        'p'    => 'Partikel',
        'pron' => 'Pronomina',
        'v'    => 'Verba',
    ];

    public function handle(): int
    {
        $filePath  = $this->argument('file');
        $userId    = (int) $this->option('user-id');
        $status    = (int) $this->option('status');
        $dryRun    = (bool) $this->option('dry-run');
        $delimiter = $this->option('delimiter');

        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");
            return 1;
        }

        if (!in_array($status, [1, 2, 3, 4])) {
            $this->error('Status tidak valid. Gunakan: 1=aktif, 2=tidak aktif, 3=menunggu, 4=menunggu finalisasi.');
            return 1;
        }

        if ($dryRun) {
            $this->warn('Mode DRY-RUN aktif — tidak ada data yang disimpan.');
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            $this->error("Tidak bisa membuka file: {$filePath}");
            return 1;
        }

        // Auto-deteksi delimiter
        if (!$delimiter) {
            $firstLine = fgets($handle);
            rewind($handle);
            $delimiter = str_contains($firstLine, "\t") ? "\t" : ',';
            $this->info('Delimiter terdeteksi: ' . ($delimiter === "\t" ? 'TAB' : 'KOMA'));
        }

        // Baca header
        $header = fgetcsv($handle, 0, $delimiter);
        if (!$header) {
            $this->error('File kosong atau tidak bisa dibaca.');
            fclose($handle);
            return 1;
        }
        $header    = array_map('trim', $header);
        $headerMap = array_flip($header);

        foreach (['lemma_normalized', 'definition'] as $required) {
            if (!array_key_exists($required, $headerMap)) {
                $this->error("Kolom wajib '{$required}' tidak ditemukan.");
                $this->line('Kolom tersedia: ' . implode(', ', $header));
                fclose($handle);
                return 1;
            }
        }

        $hasPos          = array_key_exists('pos', $headerMap);
        $hasContoh       = array_key_exists('example_expanded', $headerMap);
        $hasArtiContoh   = array_key_exists('example_indonesia', $headerMap);

        $this->info('Kolom ditemukan: ' . implode(', ', $header));
        $this->line('  ✓ Kelas kata (pos)          : ' . ($hasPos ? 'ada' : 'tidak ada'));
        $this->line('  ✓ Contoh kalimat            : ' . ($hasContoh ? 'ada (example_expanded)' : 'tidak ada'));
        $this->line('  ✓ Arti contoh (Indonesia)   : ' . ($hasArtiContoh ? 'ada (example_indonesia)' : 'tidak ada'));
        $this->newLine();

        // Baca semua baris
        $rows   = [];
        $rowNum = 1;
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNum++;
            $data = [];
            foreach ($header as $i => $col) {
                $data[$col] = isset($row[$i]) ? trim($row[$i]) : '';
            }
            $rows[] = ['num' => $rowNum, 'data' => $data];
        }
        fclose($handle);

        $total = count($rows);
        $this->info("Total baris ditemukan: {$total}");
        $this->newLine();

        // Ambil kata yang sudah ada
        $existing = Kamus::pluck('kata')->flip()->toArray();

        $imported  = 0;
        $skipped   = 0;
        $bar       = $this->output->createProgressBar($total);
        $bar->start();

        DB::beginTransaction();
        try {
            foreach ($rows as $item) {
                $rowNum  = $item['num'];
                $d       = $item['data'];

                $kata        = $d['lemma_normalized'] ?? '';
                $definisi    = $d['definition'] ?? '';
                $pos         = $hasPos ? ($d['pos'] ?? null) : null;
                $contoh      = $hasContoh ? ($d['example_expanded'] ?? '') : '';
                $artiContoh  = $hasArtiContoh ? ($d['example_indonesia'] ?? '') : '';

                if (empty($kata) || empty($definisi)) {
                    $this->newLine();
                    $this->warn("Baris {$rowNum}: Dilewati — kata atau definisi kosong.");
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                if (isset($existing[$kata])) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                if (!$dryRun) {
                    $kamus = Kamus::create([
                        'kata'       => $kata,
                        'pos'        => $pos ?: null,
                        'definisi'   => $definisi,
                        'status'     => $status,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ]);

                    if (!empty($contoh)) {
                        $kamus->contoh()->create([
                            'contoh_kalimat'      => $contoh,
                            'arti_contoh_kalimat' => $artiContoh ?: null,
                        ]);
                    }
                }

                $existing[$kata] = true;
                $imported++;
                $bar->advance();
            }

            $dryRun ? DB::rollBack() : DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $bar->finish();
            $this->newLine();
            $this->error("Import gagal di baris {$rowNum}: " . $e->getMessage());
            return 1;
        }

        $bar->finish();
        $this->newLine(2);
        $this->info($dryRun ? '[DRY-RUN] Simulasi selesai.' : 'Import selesai!');
        $this->table(
            ['Keterangan', 'Jumlah'],
            [
                ['Berhasil di-import', $imported],
                ['Dilewati (duplikat/kosong)', $skipped],
                ['Total baris', $total],
            ]
        );

        return 0;
    }
}
