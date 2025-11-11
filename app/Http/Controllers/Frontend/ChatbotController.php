<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\GeminiService;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    private $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Send chat message
     */
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'context_type' => 'nullable|string|in:general,kamus,penerjemah,pembelajaran,tentang',
            'history' => 'nullable|array'
        ]);

        try {
            $result = $this->geminiService->chatWithHistory(
                $validated['message'],
                $validated['history'] ?? [],
                $validated['context_type'] ?? 'general'
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get context options (opsional, jika diperlukan di frontend)
     */
    public function getContextOptions()
    {
        return response()->json([
            'options' => [
                'general' => 'Umum',
                'kamus' => 'Tentang Kamus',
                'penerjemah' => 'Tentang Penerjemah',
                'pembelajaran' => 'Tentang Pembelajaran',
                'tentang' => 'Tentang Website'
            ]
        ]);
    }
}
