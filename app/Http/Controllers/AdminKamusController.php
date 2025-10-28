<?php

namespace App\Http\Controllers;

use App\Models\Kamus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class AdminKamusController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view kamus', only: ['index']),
            new Middleware('permission:create kamus', only: ['create', 'store']),
            new Middleware('permission:edit kamus', only: ['edit', 'update']),
            new Middleware('permission:delete kamus', only: ['destroy']),
            new Middleware('permission:validasi kamus', only: ['validate', 'approve', 'reject']),
        ];
    }

    public function index(Request $request)
    {
        $query = Kamus::with(['creator', 'updater']);

        // Filter berdasarkan huruf A-Z jika ada parameter
        if ($request->has('letter') && $request->letter !== '') {
            $query->where('bahasa_melayu', 'LIKE', $request->letter . '%');
        }

        // Sorting berdasarkan parameter
        if ($request->has('sort') && $request->sort === 'created_at') {
            $direction = $request->direction === 'asc' ? 'asc' : 'desc';
            $query->orderBy('created_at', $direction);
        } else {
            // Default sorting
            $query->orderByRaw('CASE WHEN status = 3 THEN 0 ELSE 1 END')
                ->orderBy('bahasa_melayu', 'asc');
        }

        $kamus = $query->paginate(15)->appends($request->query());
        $letters = range('A', 'Z');

        return Inertia::render('Kamus/Index', [
            'kamus' => $kamus,
            'letters' => $letters,
            'selectedLetter' => $request->letter,
            'sort' => $request->sort,
            'direction' => $request->direction
        ]);
    }

    public function create()
    {
        return Inertia::render('Kamus/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'bahasa_melayu' => 'required|string|max:255',
            'bahasa_indonesia' => 'required|string|max:255',
            'audio' => 'nullable|file|mimes:mp3,wav,ogg|max:10240',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $hasValidationPermission = auth()->user()->can('validasi kamus');
            $status = $hasValidationPermission ? 1 : 3;

            $data = $request->except('audio');
            $data['status'] = $status;
            $data['create_by'] = auth()->id();
            $data['update_by'] = auth()->id();

            if ($request->hasFile('audio')) {
                $audioPath = $request->file('audio')->store('kamus-audio', 'public');
                $data['audio'] = $audioPath;
            }

            $kamus = Kamus::create($data);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($kamus)
                ->withProperties([
                    'status' => $status == 1 ? 'langsung aktif' : 'menunggu validasi',
                    'has_validation_permission' => $hasValidationPermission
                ])
                ->log('Created new kamus entry');

            DB::commit();

            $message = $hasValidationPermission 
                ? 'Kamus berhasil dibuat dan langsung aktif.' 
                : 'Kamus berhasil dibuat, menunggu validasi admin.';

            return redirect()->route('kamus.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal membuat kamus: ' . $e->getMessage()]);
        }
    }

    public function edit(Kamus $kamus)
    {
        return Inertia::render('Kamus/Edit', [
            'kamus' => $kamus
        ]);
    }

    public function update(Request $request, Kamus $kamus)
    {
        $request->validate([
            'bahasa_melayu' => 'required|string|max:255',
            'bahasa_indonesia' => 'required|string|max:255',
            'audio' => 'nullable|file|mimes:mp3,wav,ogg|max:10240',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $hasValidationPermission = auth()->user()->can('validasi kamus');
            
            if (!$hasValidationPermission && $kamus->status == 1) {
                $status = 3;
            } elseif ($hasValidationPermission) {
                $status = 1;
            } else {
                $status = $kamus->status;
            }

            $data = $request->except('audio');
            $data['status'] = $status;
            $data['update_by'] = auth()->id();

            if ($request->hasFile('audio')) {
                if ($kamus->audio) {
                    Storage::disk('public')->delete($kamus->audio);
                }
                $audioPath = $request->file('audio')->store('kamus-audio', 'public');
                $data['audio'] = $audioPath;
            }

            $kamus->update($data);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($kamus)
                ->withProperties([
                    'old_status' => $kamus->getOriginal('status'),
                    'new_status' => $status,
                    'has_validation_permission' => $hasValidationPermission
                ])
                ->log('Updated kamus entry');

            DB::commit();

            $message = $hasValidationPermission 
                ? 'Kamus berhasil diupdate dan langsung aktif.' 
                : 'Kamus berhasil diupdate, menunggu validasi admin.';

            return redirect()->route('kamus.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal mengupdate kamus: ' . $e->getMessage()]);
        }
    }

    public function destroy(Kamus $kamus)
    {
        DB::beginTransaction();
        try {
            $hasValidationPermission = auth()->user()->can('validasi kamus');
            
            if (!$hasValidationPermission && $kamus->status == 1) {
                return back()->withErrors(['error' => 'Anda tidak dapat menghapus kamus yang sudah aktif.']);
            }

            if ($kamus->audio) {
                Storage::disk('public')->delete($kamus->audio);
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($kamus)
                ->withProperties(['deleted_kamus' => $kamus->toArray()])
                ->log('Deleted kamus entry');

            $kamus->delete();

            DB::commit();

            return redirect()->route('kamus.index')->with('success', 'Kamus berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menghapus kamus: ' . $e->getMessage()]);
        }
    }

    public function validate()
    {
        //
    }

    public function approve(Kamus $kamus)
    {
        DB::beginTransaction();
        try {
            $kamus->update([
                'status' => 1,
                'update_by' => auth()->id(),
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($kamus)
                ->log('Approved kamus entry');

            DB::commit();

            return back()->with('success', 'Kamus berhasil disetujui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyetujui kamus: ' . $e->getMessage()]);
        }
    }

    public function reject(Kamus $kamus)
    {
        DB::beginTransaction();
        try {
            $kamus->update([
                'status' => 2,
                'update_by' => auth()->id(),
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($kamus)
                ->log('Rejected kamus entry');

            DB::commit();

            return back()->with('success', 'Kamus berhasil ditolak.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menolak kamus: ' . $e->getMessage()]);
        }
    }
}