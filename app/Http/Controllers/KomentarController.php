<?php

namespace App\Http\Controllers;

use App\Models\Komentar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;


class KomentarController extends Controller
{

    public function index(Request $request)
    {
        $query = Komentar::with('commentable')
            ->orderBy('created_at', 'desc');

        // Filter berdasarkan status (optional)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $komentars = $query->paginate(15)->withQueryString();

        return Inertia::render('Komentar/Index', [
            'komentars' => $komentars,
            'filters' => $request->only('status'),
        ]);
    }

    public function approve(Komentar $komentar)
    {
        $komentar->update(['status' => 'approved']);
        return back()->with('success', 'Komentar berhasil disetujui.');
    }

    public function reject(Komentar $komentar)
    {
        $komentar->update(['status' => 'rejected']);
        return back()->with('success', 'Komentar berhasil ditolak.');
    }

    public function destroy(Komentar $komentar)
    {
        $komentar->delete();
        return back()->with('success', 'Komentar berhasil dihapus.');
    }

    /**
     * Menyimpan komentar baru dari pengunjung (tanpa login)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'kontak' => 'nullable|string|max:255',
            'isi_komentar' => 'required|string|max:2000',
            'commentable_type' => 'required|string|in:App\Models\Artikel,App\Models\ModulPembelajaran',
            'commentable_id' => 'required|integer|min:1',
        ]);

        // Tambahkan validasi custom untuk exists berdasarkan tipe
        $validator->after(function ($validator) use ($request) {
            $type = $request->commentable_type;
            $id = $request->commentable_id;

            if ($type === 'App\Models\Artikel') {
                if (!\App\Models\Artikel::where('id', $id)->where('status', 'published')->exists()) {
                    $validator->errors()->add('commentable_id', 'Artikel tidak ditemukan.');
                }
            } elseif ($type === 'App\Models\ModulPembelajaran') {
                if (!\App\Models\ModulPembelajaran::where('id', $id)->where('status', 'published')->exists()) {
                    $validator->errors()->add('commentable_id', 'Modul pembelajaran tidak ditemukan.');
                }
            }
        });

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $komentar = \App\Models\Komentar::create([
            'nama' => $request->nama,
            'kontak' => $request->kontak,
            'isi_komentar' => $request->isi_komentar,
            'commentable_type' => $request->commentable_type,
            'commentable_id' => $request->commentable_id,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Komentar berhasil dikirim dan menunggu moderasi.');
    }
}