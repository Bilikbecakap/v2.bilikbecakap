<?php

namespace App\Http\Controllers;

use App\Models\FeedbackTerjemahan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeedbackTerjemahanController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'tipe'              => 'required|in:akurat,kurang_tepat',
            'arah_terjemahan'   => 'required|string',
            'teks_input'        => 'required|string|max:10000',
            'terjemahan_asli'   => 'required|string|max:10000',
            'terjemahan_benar'  => 'nullable|string|max:10000',
            'keterangan'        => 'nullable|string|max:1000',
        ]);

        FeedbackTerjemahan::create([
            'tipe'             => $request->tipe,
            'arah_terjemahan'  => $request->arah_terjemahan,
            'teks_input'       => $request->teks_input,
            'terjemahan_asli'  => $request->terjemahan_asli,
            'terjemahan_benar' => $request->terjemahan_benar,
            'keterangan'       => $request->keterangan,
            'ip_address'       => $request->ip(),
        ]);

        return response()->json(['success' => true, 'message' => 'Feedback berhasil dikirim, terima kasih!']);
    }

    public function index(Request $request)
    {
        $query = FeedbackTerjemahan::orderBy('created_at', 'desc');

        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        if ($request->filled('arah')) {
            $query->where('arah_terjemahan', $request->arah);
        }

        $feedbacks = $query->paginate(20)->withQueryString();

        return Inertia::render('FeedbackTerjemahan/Index', [
            'feedbacks' => $feedbacks,
            'filters'   => $request->only('tipe', 'arah'),
        ]);
    }

    public function destroy(FeedbackTerjemahan $feedbackTerjemahan)
    {
        $feedbackTerjemahan->delete();
        return back()->with('success', 'Feedback berhasil dihapus.');
    }
}
