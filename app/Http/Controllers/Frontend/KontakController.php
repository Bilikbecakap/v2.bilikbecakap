<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Kontak;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KontakController extends Controller
{
    /**
     * Tampilkan halaman kontak publik
     */
    public function index()
    {
        return Inertia::render('Frontend/Kontak');
    }

    /**
     * Simpan pesan kontak
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'nomor_telepon' => 'nullable|string|max:20',
            'subjek' => 'required|string|max:255',
            'pesan' => 'required|string|min:10',
        ]);

        try {
            Kontak::create($request->all());
            return redirect()->route('kontak.index')->with('success', 'Pesan kontak berhasil dikirim! Kami akan segera menghubungi Anda.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Gagal mengirim pesan: ' . $e->getMessage()]);
        }
    }

    /**
     * Tampilkan semua pesan kontak masuk (Admin)
     */
    public function adminIndex()
    {
        $kontaks = Kontak::latest()->paginate(15);
        
        return Inertia::render('Kontak/Index', [
            'kontaks' => $kontaks,
        ]);
    }

    /**
     * Tampilkan detail pesan kontak (Admin)
     */
    public function adminShow(Kontak $kontak)
    {
        return Inertia::render('Kontak/Show', [
            'kontak' => $kontak,
        ]);
    }

    /**
     * Hapus pesan kontak (Admin)
     */
    public function adminDestroy(Kontak $kontak)
    {
        $kontak->delete();

        return redirect()->route('admin.kontak.index')->with('success', 'Pesan kontak berhasil dihapus.');
    }

    /**
     * Hapus beberapa pesan kontak secara bersamaan (Admin)
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:kontaks,id',
        ])['ids'];

        Kontak::whereIn('id', $ids)->delete();

        return redirect()->route('admin.kontak.index')->with('success', 'Pesan kontak berhasil dihapus.');
    }
}