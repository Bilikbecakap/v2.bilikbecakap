<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterGambar;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class MasterGambarController extends Controller implements HasMiddleware
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
        $gambar = MasterGambar::orderBy('urutan', 'asc')->get();

        return Inertia::render('DataMaster/MasterGambar/Index', [
            'gambar' => $gambar
        ]);
    }

    public function create()
    {
        return Inertia::render('DataMaster/MasterGambar/Create');
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
            $gambar = MasterGambar::create($request->all());

            activity()
                ->causedBy(auth()->user())
                ->performedOn($gambar)
                ->log('Created new master gambar');

            DB::commit();

            return redirect()->route('data-master.gambar.index')->with('success', 'Master Gambar berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal membuat master gambar: ' . $e->getMessage()]);
        }
    }

    public function edit(MasterGambar $gambar)
    {
        return Inertia::render('DataMaster/MasterGambar/Edit', [
            'gambar' => $gambar
        ]);
    }

    public function update(Request $request, MasterGambar $gambar)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
            'urutan' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $gambar->update($request->all());

            activity()
                ->causedBy(auth()->user())
                ->performedOn($gambar)
                ->log('Updated master gambar');

            DB::commit();

            return redirect()->route('data-master.gambar.index')->with('success', 'Master Gambar berhasil diupdate.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal mengupdate master gambar: ' . $e->getMessage()]);
        }
    }

    public function destroy(MasterGambar $gambar)
    {
        DB::beginTransaction();
        try {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($gambar)
                ->withProperties(['deleted_gambar' => $gambar->toArray()])
                ->log('Deleted master gambar');

            $gambar->delete();

            DB::commit();

            return redirect()->route('data-master.gambar.index')->with('success', 'Master Gambar berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menghapus master gambar: ' . $e->getMessage()]);
        }
    }
}