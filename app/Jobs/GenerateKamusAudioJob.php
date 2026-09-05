<?php

namespace App\Jobs;

use App\Models\Kamus;
use App\Models\KamusAudioGenerationBatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class GenerateKamusAudioJob implements ShouldQueue
{
    use Queueable;

    public $tries = 1;

    public $timeout = 0;

    public function __construct(
        public int $batchId,
        public string $voice = 'ms-MY-OsmanNeural',
    ) {
    }

    public function handle(): void
    {
        $batch = KamusAudioGenerationBatch::find($this->batchId);

        if (!$batch) {
            return;
        }

        $words = Kamus::where(function ($q) {
            $q->whereNull('audio')->orWhere('audio', '');
        })->get(['id', 'kata']);

        if ($words->isEmpty()) {
            $batch->update([
                'status'      => 'completed',
                'total_words' => 0,
                'started_at'  => now(),
                'finished_at' => now(),
            ]);
            return;
        }

        $tmpDir    = storage_path('app/tmp/kamus-audio-gen-' . $batch->id);
        $outputDir = $tmpDir . '/output';
        File::ensureDirectoryExists($outputDir);

        $inputCsv = $tmpDir . '/input.csv';
        $fh = fopen($inputCsv, 'w');
        foreach ($words as $word) {
            fputcsv($fh, [$word->id, $word->kata]);
        }
        fclose($fh);

        $batch->update([
            'status'      => 'running',
            'total_words' => $words->count(),
            'started_at'  => now(),
        ]);

        $python = env('PYTHON_BIN', 'python3');
        $script = base_path('scripts/generate_kamus_audio.py');

        $process = new Process([
            $python,
            $script,
            '--input', $inputCsv,
            '--output', $outputDir,
            '--voice', $this->voice,
        ]);
        $process->setTimeout(null);

        $buffer    = '';
        $processed = 0;
        $success   = 0;
        $skipped   = 0;
        $failed    = 0;
        $errors    = [];

        $process->start();

        foreach ($process as $type => $data) {
            if ($type !== Process::OUT) {
                continue;
            }

            $buffer .= $data;

            while (($pos = strpos($buffer, "\n")) !== false) {
                $line = trim(substr($buffer, 0, $pos));
                $buffer = substr($buffer, $pos + 1);

                if ($line === '') {
                    continue;
                }

                $item = json_decode($line, true);
                if (!is_array($item) || !isset($item['status'], $item['id'])) {
                    continue;
                }

                $processed++;
                $status = $item['status'];

                if ($status === 'success') {
                    $sourceFile = $outputDir . '/' . $item['id'] . '.mp3';

                    if (is_file($sourceFile)) {
                        $destination = 'kamus-audio/' . $item['id'] . '.mp3';
                        Storage::disk('public')->put($destination, file_get_contents($sourceFile));
                        Kamus::where('id', $item['id'])->update(['audio' => $destination]);
                        @unlink($sourceFile);
                        $success++;
                    } else {
                        $failed++;
                        if (count($errors) < 50) {
                            $errors[] = ($item['kata'] ?? '?') . ': file audio tidak ditemukan setelah generate';
                        }
                    }
                } elseif ($status === 'skipped') {
                    $skipped++;
                } else {
                    $failed++;
                    if (count($errors) < 50) {
                        $errors[] = ($item['kata'] ?? '?') . ': ' . $status;
                    }
                }

                if ($processed % 20 === 0) {
                    $batch->update([
                        'processed'      => $processed,
                        'success_count'  => $success,
                        'skipped_count'  => $skipped,
                        'failed_count'   => $failed,
                    ]);
                }
            }
        }

        $exitCode = $process->wait();

        $batch->update([
            'processed'     => $processed,
            'success_count' => $success,
            'skipped_count' => $skipped,
            'failed_count'  => $failed,
            'status'        => $exitCode === 0 ? 'completed' : 'failed',
            'error_message' => $exitCode === 0 ? null : substr($process->getErrorOutput(), 0, 2000),
            'error_log'     => empty($errors) ? null : implode("\n", $errors),
            'finished_at'   => now(),
        ]);

        File::deleteDirectory($tmpDir);
    }

    public function failed(\Throwable $exception): void
    {
        $batch = KamusAudioGenerationBatch::find($this->batchId);

        if ($batch && $batch->status !== 'completed') {
            $batch->update([
                'status'        => 'failed',
                'error_message' => substr($exception->getMessage(), 0, 2000),
                'finished_at'   => now(),
            ]);
        }

        File::deleteDirectory(storage_path('app/tmp/kamus-audio-gen-' . $this->batchId));
    }
}
