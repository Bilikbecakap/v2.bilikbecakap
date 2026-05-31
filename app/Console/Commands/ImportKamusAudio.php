<?php

namespace App\Console\Commands;

use App\Models\Kamus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportKamusAudio extends Command
{
    protected $signature = 'kamus:import-audio
                            {folder : Path ke folder berisi file audio}
                            {--overwrite : Timpa audio yang sudah ada}
                            {--dry-run : Simulasi tanpa menyimpan}';

    protected $description = 'Bulk import audio ke kamus berdasarkan nama file = kata';

    private const SUPPORTED = ['m4a', 'mp3', 'wav', 'ogg', 'webm'];

    public function handle(): int
    {
        $folder  = rtrim($this->argument('folder'), '/\\');
        $dryRun  = $this->option('dry-run');
        $overwrite = $this->option('overwrite');

        if (!is_dir($folder)) {
            $this->error("Folder tidak ditemukan: {$folder}");
            return 1;
        }

        if ($dryRun) {
            $this->warn('Mode DRY-RUN aktif — tidak ada perubahan yang disimpan.');
        }

        // Scan semua file audio di folder
        $files = array_filter(scandir($folder), function ($file) use ($folder) {
            if (in_array($file, ['.', '..'])) return false;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            return in_array($ext, self::SUPPORTED) && is_file("{$folder}/{$file}");
        });

        $total    = count($files);
        $matched  = 0;
        $skipped  = 0;
        $notFound = 0;

        $this->info("Ditemukan {$total} file audio.");
        $this->newLine();

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($files as $file) {
            $kata = pathinfo($file, PATHINFO_FILENAME); // nama file tanpa ekstensi
            $ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            $kamus = Kamus::where('kata', $kata)->first();

            if (!$kamus) {
                $notFound++;
                $bar->advance();
                continue;
            }

            if ($kamus->audio && !$overwrite) {
                $skipped++;
                $bar->advance();
                continue;
            }

            if (!$dryRun) {
                // Hapus audio lama kalau ada
                if ($kamus->audio) {
                    Storage::disk('public')->delete($kamus->audio);
                }

                $destination = "kamus-audio/{$kata}.{$ext}";
                $sourcePath  = "{$folder}/{$file}";

                // Salin file ke storage public
                Storage::disk('public')->put(
                    $destination,
                    file_get_contents($sourcePath)
                );

                $kamus->update(['audio' => $destination]);
            }

            $matched++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info($dryRun ? '[DRY-RUN] Simulasi selesai.' : 'Import audio selesai!');
        $this->table(
            ['Keterangan', 'Jumlah'],
            [
                ['Berhasil dicocokkan & disimpan', $matched],
                ['Dilewati (sudah ada audio, tanpa --overwrite)', $skipped],
                ['Kata tidak ditemukan di database', $notFound],
                ['Total file', $total],
            ]
        );

        if ($notFound > 0) {
            $this->newLine();
            $this->warn("Ada {$notFound} file yang tidak cocok dengan kata di database. Pastikan nama file sama persis dengan kolom 'kata'.");
        }

        return 0;
    }
}
