<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterModul;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class MasterModulController extends Controller implements HasMiddleware
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
        $modul = MasterModul::orderBy('urutan', 'asc')->get();

        return Inertia::render('DataMaster/MasterModul/Index', [
            'modul' => $modul
        ]);
    }

    public function create()
    {
        return Inertia::render('DataMaster/MasterModul/Create');
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
            $modul = MasterModul::create($request->all());

            activity()
                ->causedBy(auth()->user())
                ->performedOn($modul)
                ->log('Created new master modul');

            DB::commit();

            return redirect()->route('data-master.modul.index')->with('success', 'Master Modul berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal membuat master modul: ' . $e->getMessage()]);
        }
    }

    public function edit(MasterModul $modul)
    {
        return Inertia::render('DataMaster/MasterModul/Edit', [
            'modul' => $modul
        ]);
    }

    public function update(Request $request, MasterModul $modul)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
            'urutan' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $modul->update($request->all());

            activity()
                ->causedBy(auth()->user())
                ->performedOn($modul)
                ->log('Updated master modul');

            DB::commit();

            return redirect()->route('data-master.modul.index')->with('success', 'Master Modul berhasil diupdate.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal mengupdate master modul: ' . $e->getMessage()]);
        }
    }

    public function destroy(MasterModul $modul)
    {
        DB::beginTransaction();
        try {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($modul)
                ->withProperties(['deleted_modul' => $modul->toArray()])
                ->log('Deleted master modul');

            $modul->delete();

            DB::commit();

            return redirect()->route('data-master.modul.index')->with('success', 'Master Modul berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menghapus master modul: ' . $e->getMessage()]);
        }
    }
}