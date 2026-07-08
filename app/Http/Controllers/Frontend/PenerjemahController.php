<?php

namespace App\Http\Controllers\Frontend;

use App\Contracts\AIServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PenerjemahController extends Controller
{
    protected AIServiceInterface $geminiService;

    public function __construct(AIServiceInterface $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function index()
    {
        return Inertia::render('Frontend/Penerjemah', [
            'user' => auth()->user(),
        ]);
    }

    public function translate(Request $request)
    {
        $request->validate([
            'text'      => 'required|string|max:10000',
            'direction' => 'required|in:belitung_to_indonesia,indonesia_to_belitung,belitung_to_english,english_to_belitung',
        ], [
            'text.required'      => 'Teks yang akan diterjemahkan wajib diisi.',
            'text.max'           => 'Teks maksimal 10000 karakter.',
            'direction.required' => 'Arah terjemahan wajib dipilih.',
            'direction.in'       => 'Arah terjemahan tidak valid.',
        ]);

        $directionMap = [
            'belitung_to_indonesia' => 'bel-to-id',
            'indonesia_to_belitung' => 'id-to-bel',
            'belitung_to_english'   => 'bel-to-en',
            'english_to_belitung'   => 'en-to-bel',
        ];

        try {
            $startTime = microtime(true);

            $response = Http::timeout(30)->post('http://localhost:8000/translate', [
                'text'      => $request->text,
                'direction' => $directionMap[$request->direction],
            ]);

            if (!$response->successful()) {
                $error = $response->json('detail') ?? 'API error';
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menerjemahkan: ' . $error,
                ], 422);
            }

            $apiData = $response->json();
            $processingTime = round((microtime(true) - $startTime) * 1000, 2);

            return response()->json([
                'success' => true,
                'data'    => [
                    'input'              => $apiData['input'],
                    'translation'        => $apiData['translation'],
                    'direction'          => $request->direction,
                    'method'             => 'api',
                    'confidence'         => 'high',
                    'processing_time_ms' => $processingTime,
                    'word_count'         => str_word_count(trim($request->text)),
                    'ai_used'            => true,
                ],
                'message' => 'Terjemahan berhasil!',
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan terjemahan tidak tersedia. Silakan coba lagi.',
            ], 503);
        } catch (\Exception $e) {
            Log::error('Translation error: ' . $e->getMessage(), [
                'input'     => $request->text,
                'direction' => $request->direction,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.',
            ], 500);
        }
    }

    public function extractText(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        try {
            Log::info('OCR Extract Started');

            $image    = $request->file('image');
            $path     = $image->store('ocr-temp', 'public');
            $fullPath = storage_path('app/public/' . $path);

            $result = $this->geminiService->extractTextFromImage($fullPath);

            @unlink($fullPath);

            Log::info('OCR Extract Complete', ['success' => $result['success']]);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('OCR Extract Error', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
