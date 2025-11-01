<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterArtikel;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class MasterArtikelController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view data master', only: ['index']),
            new Middleware('permission:create data master', only: ['create', 'store']),
            new Middleware('permission:edit data master', only: ['edit', 'update']),
            new Middleware('permission:delete data master', only: ['destroy']),
        ];
    }

    public function index()
    {
        $artikel = MasterArtikel::orderBy('urutan', 'asc')->get();

        return Inertia::render('DataMaster/MasterArtikel/Index', [
            'artikel' => $artikel
        ]);
    }

    public function create()
    {
        return Inertia::render('DataMaster/MasterArtikel/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
            'urutan' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $artikel = MasterArtikel::create($request->all());

            activity()
                ->causedBy(auth()->user())
                ->performedOn($artikel)
                ->log('Created new master artikel');

            DB::commit();

            return redirect()->route('data-master.artikel.index')->with('success', 'Master Artikel berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal membuat master artikel: ' . $e->getMessage()]);
        }
    }

    public function edit(MasterArtikel $artikel)
    {
        return Inertia::render('DataMaster/MasterArtikel/Edit', [
            'artikel' => $artikel
        ]);
    }

    public function update(Request $request, MasterArtikel $artikel)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
            'urutan' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $artikel->update($request->all());

            activity()
                ->causedBy(auth()->user())
                ->performedOn($artikel)
                ->log('Updated master artikel');

            DB::commit();

            return redirect()->route('data-master.artikel.index')->with('success', 'Master Artikel berhasil diupdate.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal mengupdate master artikel: ' . $e->getMessage()]);
        }
    }

    public function destroy(MasterArtikel $artikel)
    {
        DB::beginTransaction();
        try {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($artikel)
                ->withProperties(['deleted_artikel' => $artikel->toArray()])
                ->log('Deleted master artikel');

            $artikel->delete();

            DB::commit();

            return redirect()->route('data-master.artikel.index')->with('success', 'Master Artikel berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menghapus master artikel: ' . $e->getMessage()]);
        }
    }
}
