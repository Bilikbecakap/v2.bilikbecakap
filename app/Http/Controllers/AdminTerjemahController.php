<?php

namespace App\Http\Controllers;

use App\Models\TerjemahPengujian;
use App\Models\TerjemahPengujianValidasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class AdminTerjemahController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:create terjemah', only: ['create', 'store']),
            new Middleware('permission:edit terjemah',   only: ['edit', 'update']),
            new Middleware('permission:validasi terjemah',   only: ['validasiTerjemah']),
            new Middleware('permission:finalisasi terjemah', only: ['finalisasiTerjemah']),
        ];
    }

    public function index(Request $request)
    {
        $user                    = auth()->user();
        $hasValidasiPermission   = $user->can('validasi terjemah');
        $hasFinalisasiPermission = $user->can('finalisasi terjemah');

        $query = TerjemahPengujian::with(['creator', 'validasi.validator']);

        // User biasa hanya lihat milik sendiri + yang tervalidasi
        if (!$hasValidasiPermission && !$hasFinalisasiPermission) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('status', 3);
            });
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('teks_indonesia', 'LIKE', "%{$s}%")
                  ->orWhere('terjemahan_pengguna', 'LIKE', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $query->orderByRaw('CASE WHEN status IN (1,2) THEN 0 ELSE 1 END')
              ->orderByDesc('created_at');

        $data = $query->paginate(15)->appends($request->query());

        return Inertia::render('Terjemah/Index', [
            'data'                    => $data,
            'search'                  => $request->search,
            'status'                  => $request->status,
            'currentUserId'           => $user->id,
            'hasValidasiPermission'   => $hasValidasiPermission,
            'hasFinalisasiPermission' => $hasFinalisasiPermission,
        ]);
    }

    public function create()
    {
        return Inertia::render('Terjemah/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'teks_indonesia'      => 'required|string',
            'terjemahan_pengguna' => 'required|string',
        ], [
            'teks_indonesia.required'      => 'Teks Bahasa Indonesia wajib diisi.',
            'terjemahan_pengguna.required' => 'Terjemahan wajib diisi.',
        ]);

        TerjemahPengujian::create([
            'teks_indonesia'      => $request->teks_indonesia,
            'terjemahan_pengguna' => $request->terjemahan_pengguna,
            'status'              => 1,
            'created_by'          => auth()->id(),
            'updated_by'          => auth()->id(),
        ]);

        return redirect()->route('terjemah.index')->with('success', 'Pengujian berhasil dikirim, menunggu validasi.');
    }

    public function edit(TerjemahPengujian $terjemah)
    {
        $user    = auth()->user();
        $isOwner = $terjemah->created_by === $user->id;

        if (!$isOwner && !$user->can('finalisasi terjemah')) {
            return back()->withErrors(['error' => 'Anda tidak memiliki izin untuk mengedit ini.']);
        }

        if ($isOwner && !in_array($terjemah->status, [1, 4])) {
            return back()->withErrors(['error' => 'Tidak dapat mengedit data yang sudah divalidasi.']);
        }

        return Inertia::render('Terjemah/Edit', [
            'terjemah' => $terjemah,
        ]);
    }

    public function update(Request $request, TerjemahPengujian $terjemah)
    {
        $user    = auth()->user();
        $isOwner = $terjemah->created_by === $user->id;

        if (!$isOwner && !$user->can('finalisasi terjemah')) {
            return back()->withErrors(['error' => 'Anda tidak memiliki izin untuk mengedit ini.']);
        }

        if ($isOwner && !in_array($terjemah->status, [1, 4])) {
            return back()->withErrors(['error' => 'Tidak dapat mengedit data yang sudah divalidasi.']);
        }

        $request->validate([
            'teks_indonesia'      => 'required|string',
            'terjemahan_pengguna' => 'required|string',
        ]);

        $newStatus = $terjemah->status;

        // Jika owner edit setelah ditolak → reset ke menunggu
        if ($isOwner && $terjemah->status === 4) {
            TerjemahPengujianValidasi::where('terjemah_pengujian_id', $terjemah->id)->delete();
            $newStatus = 1;
        }

        $terjemah->update([
            'teks_indonesia'      => $request->teks_indonesia,
            'terjemahan_pengguna' => $request->terjemahan_pengguna,
            'status'              => $newStatus,
            'updated_by'          => $user->id,
        ]);

        return redirect()->route('terjemah.index')->with('success', 'Pengujian berhasil diupdate.');
    }

    public function destroy(TerjemahPengujian $terjemah)
    {
        $user    = auth()->user();
        $isOwner = $terjemah->created_by === $user->id;

        if (!$isOwner && !$user->can('finalisasi terjemah')) {
            return back()->withErrors(['error' => 'Anda tidak memiliki izin untuk menghapus ini.']);
        }

        // Owner hanya bisa hapus miliknya yang belum/ditolak
        if ($isOwner && !$user->can('finalisasi terjemah') && !in_array($terjemah->status, [1, 4])) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus data yang sedang dalam proses validasi.']);
        }

        $terjemah->delete();

        return redirect()->route('terjemah.index')->with('success', 'Pengujian berhasil dihapus.');
    }

    public function tinjauan(TerjemahPengujian $terjemah)
    {
        $user                    = auth()->user();
        $hasValidasiPermission   = $user->can('validasi terjemah');
        $hasFinalisasiPermission = $user->can('finalisasi terjemah');
        $isOwner                 = $terjemah->created_by === $user->id;

        if (!$isOwner && !$hasValidasiPermission && !$hasFinalisasiPermission) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $terjemah->load(['creator', 'validasi.validator']);

        $sudahDivalidasi = $terjemah->validasi !== null;
        $bisaValidasi    = $hasValidasiPermission && $terjemah->status === 1 && !$sudahDivalidasi;
        $bisaFinalisasi  = $hasFinalisasiPermission && $terjemah->status === 2;

        return Inertia::render('Terjemah/Tinjauan', [
            'terjemah'               => $terjemah,
            'bisaValidasi'           => $bisaValidasi,
            'bisaFinalisasi'         => $bisaFinalisasi,
            'sudahDivalidasi'        => $sudahDivalidasi,
            'hasValidasiPermission'  => $hasValidasiPermission,
            'hasFinalisasiPermission'=> $hasFinalisasiPermission,
            'isOwner'                => $isOwner,
        ]);
    }

    public function validasiTerjemah(Request $request, TerjemahPengujian $terjemah)
    {
        if ($terjemah->status !== 1) {
            return back()->withErrors(['error' => 'Data ini tidak dalam status menunggu validasi.']);
        }

        if ($terjemah->validasi) {
            return back()->withErrors(['error' => 'Data ini sudah divalidasi.']);
        }

        $request->validate([
            'terjemahan_koreksi' => 'nullable|string',
            'catatan'            => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            TerjemahPengujianValidasi::create([
                'terjemah_pengujian_id' => $terjemah->id,
                'user_id'               => auth()->id(),
                'terjemahan_koreksi'    => $request->terjemahan_koreksi ?: null,
                'catatan'               => $request->catatan ?: null,
            ]);

            $terjemah->update([
                'status'     => 2,
                'updated_by' => auth()->id(),
            ]);

            DB::commit();
            return redirect()->route('terjemah.index')->with('success', 'Validasi berhasil! Data menunggu finalisasi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyimpan validasi: ' . $e->getMessage()]);
        }
    }

    public function finalisasiTerjemah(Request $request, TerjemahPengujian $terjemah)
    {
        if ($terjemah->status !== 2) {
            return back()->withErrors(['error' => 'Data ini tidak dalam status menunggu finalisasi.']);
        }

        $request->validate([
            'action'  => 'required|in:publish,tolak',
            'catatan' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            if ($request->action === 'publish') {
                $terjemah->update([
                    'status'     => 3,
                    'updated_by' => auth()->id(),
                ]);

                DB::commit();
                return redirect()->route('terjemah.index')->with('success', 'Data berhasil dipublikasikan!');
            }

            // Tolak → hapus validasi, kembali ke menunggu
            TerjemahPengujianValidasi::where('terjemah_pengujian_id', $terjemah->id)->delete();
            $terjemah->update([
                'status'     => 4,
                'updated_by' => auth()->id(),
            ]);

            DB::commit();
            return redirect()->route('terjemah.index')->with('success', 'Data ditolak dan dikembalikan ke pemilik.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal melakukan finalisasi: ' . $e->getMessage()]);
        }
    }
}
