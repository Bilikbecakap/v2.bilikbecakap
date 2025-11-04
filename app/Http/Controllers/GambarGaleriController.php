<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GambarGaleri;
use App\Models\MasterGambar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class GambarGaleriController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view galeri', only: ['index', 'show']),
            new Middleware('permission:create galeri', only: ['create', 'store']),
            new Middleware('permission:edit galeri', only: ['edit', 'update']),
            new Middleware('permission:delete galeri', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = GambarGaleri::with('masterGambar');

        // Filter berdasarkan kategori
        if ($request->filled('kategori')) {
            $query->where('master_gambar_id', $request->kategori);
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $isActive = $request->status === 'active' ? true : false;
            $query->where('is_active', $isActive);
        }

        // Search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where('keterangan', 'LIKE', '%' . $searchTerm . '%');
        }

        $galeri = $query->orderBy('created_at', 'desc')
            ->paginate(12)
            ->appends($request->query());

        $kategoris = MasterGambar::where('is_active', true)
            ->orderBy('urutan', 'asc')
            ->get(['id', 'nama_kategori']);

        return Inertia::render('Galeri/Index', [
            'galeri' => $galeri,
            'kategoris' => $kategoris,
            'filters' => [
                'kategori' => $request->kategori,
                'status' => $request->status,
                'search' => $request->search,
            ],
        ]);
    }

    public function create()
    {
        $kategoris = MasterGambar::where('is_active', true)
            ->orderBy('urutan', 'asc')
            ->get(['id', 'nama_kategori']);

        return Inertia::render('Galeri/Create', [
            'kategoris' => $kategoris
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'gambar' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120', // max 5MB
            'master_gambar_id' => 'required|exists:master_gambar,id',
            'keterangan' => 'nullable|string',
            'is_active' => 'boolean',
        ], [
            'gambar.required' => 'Gambar wajib diupload.',
            'gambar.image' => 'File harus berupa gambar.',
            'gambar.mimes' => 'Gambar harus berformat jpeg, jpg, png, atau webp.',
            'gambar.max' => 'Ukuran gambar maksimal 5MB.',
            'master_gambar_id.required' => 'Kategori gambar wajib dipilih.',
            'master_gambar_id.exists' => 'Kategori gambar tidak valid.',
        ]);

        DB::beginTransaction();
        try {
            $gambarPath = null;
            
            if ($request->hasFile('gambar')) {
                $file = $request->file('gambar');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $gambarPath = $file->storeAs('galeri/images', $fileName, 'public');
            }

            $galeri = GambarGaleri::create([
                'gambar' => $gambarPath,
                'master_gambar_id' => $request->master_gambar_id,
                'keterangan' => $request->keterangan,
                'is_active' => $request->is_active ?? true,
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($galeri)
                ->log('Created new gambar galeri');

            DB::commit();

            return redirect()->route('galeri.index')->with('success', 'Gambar galeri berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Hapus file jika ada error
            if ($gambarPath && Storage::disk('public')->exists($gambarPath)) {
                Storage::disk('public')->delete($gambarPath);
            }
            
            return back()->withInput()->withErrors(['error' => 'Gagal menambahkan gambar: ' . $e->getMessage()]);
        }
    }

    public function edit(GambarGaleri $galeri)
    {
        $galeri->load('masterGambar');

        $kategoris = MasterGambar::where('is_active', true)
            ->orderBy('urutan', 'asc')
            ->get(['id', 'nama_kategori']);

        return Inertia::render('Galeri/Edit', [
            'galeri' => $galeri,
            'kategoris' => $kategoris
        ]);
    }

    public function update(Request $request, GambarGaleri $galeri)
    {
        $request->validate([
            'gambar' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120', // max 5MB
            'master_gambar_id' => 'required|exists:master_gambar,id',
            'keterangan' => 'nullable|string',
            'is_active' => 'boolean',
            'remove_gambar' => 'nullable|boolean',
        ], [
            'gambar.image' => 'File harus berupa gambar.',
            'gambar.mimes' => 'Gambar harus berformat jpeg, jpg, png, atau webp.',
            'gambar.max' => 'Ukuran gambar maksimal 5MB.',
            'master_gambar_id.required' => 'Kategori gambar wajib dipilih.',
            'master_gambar_id.exists' => 'Kategori gambar tidak valid.',
        ]);

        DB::beginTransaction();
        try {
            $data = [
                'master_gambar_id' => $request->master_gambar_id,
                'keterangan' => $request->keterangan,
                'is_active' => $request->is_active ?? true,
            ];

            // Handle remove gambar
            if ($request->remove_gambar) {
                $galeri->deleteGambar();
                $data['gambar'] = null;
            }

            // Handle upload gambar baru
            if ($request->hasFile('gambar')) {
                // Hapus gambar lama
                $galeri->deleteGambar();
                
                // Upload gambar baru
                $file = $request->file('gambar');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $gambarPath = $file->storeAs('galeri/images', $fileName, 'public');
                $data['gambar'] = $gambarPath;
            }

            $galeri->update($data);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($galeri)
                ->log('Updated gambar galeri');

            DB::commit();

            return redirect()->route('galeri.index')->with('success', 'Gambar galeri berhasil diupdate.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal mengupdate gambar: ' . $e->getMessage()]);
        }
    }

    public function destroy(GambarGaleri $galeri)
    {
        DB::beginTransaction();
        try {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($galeri)
                ->withProperties(['deleted_galeri' => $galeri->toArray()])
                ->log('Deleted gambar galeri');

            // Hapus file gambar
            $galeri->deleteGambar();

            $galeri->delete();

            DB::commit();

            return redirect()->route('galeri.index')->with('success', 'Gambar galeri berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menghapus gambar: ' . $e->getMessage()]);
        }
    }
}