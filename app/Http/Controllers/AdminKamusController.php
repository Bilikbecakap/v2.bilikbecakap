<?php

namespace App\Http\Controllers;

use App\Models\Kamus;
use App\Models\KamusValidasi;
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
            new Middleware('permission:validasi kamus', only: ['validasiKamus']),
            new Middleware('permission:finalisasi kamus', only: ['finalisasiKamus']),
        ];
    }

    public function index(Request $request)
    {
        $query = Kamus::with(['creator', 'updater']);

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('kata', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('definisi', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('catatan_validasi', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('sort') && $request->sort === 'created_at') {
            $direction = $request->direction === 'asc' ? 'asc' : 'desc';
            $query->orderBy('created_at', $direction);
        } else {
            $query->orderByRaw('CASE WHEN status IN (3,4) THEN 0 ELSE 1 END')
                ->orderBy('kata', 'asc');
        }

        $kamus = $query->paginate(15)->appends($request->query());

        $currentUserId = auth()->id();
        $hasValidationPermission = auth()->user()->can('validasi kamus');
        $hasFinalizePermission = auth()->user()->can('finalisasi kamus');

        $kamusData = $kamus->getCollection()->map(function ($item) use ($currentUserId, $hasValidationPermission, $hasFinalizePermission) {
            $item->can_edit   = $hasValidationPermission || $item->created_by == $currentUserId;
            $item->can_delete = $hasFinalizePermission   || $item->created_by == $currentUserId;
            $item->can_tinjau = ($hasValidationPermission && $item->status == 3) || ($hasFinalizePermission && $item->status == 4);
            return $item;
        });

        $kamus->setCollection($kamusData);

        return Inertia::render('Kamus/Index', [
            'kamus'                   => $kamus,
            'search'                  => $request->search,
            'status'                  => $request->status,
            'sort'                    => $request->sort,
            'direction'               => $request->direction,
            'hasValidationPermission' => $hasValidationPermission,
            'hasFinalizePermission'   => $hasFinalizePermission,
            'currentUserId'           => $currentUserId,
        ]);
    }

    public function create()
    {
        return Inertia::render('Kamus/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kata'             => 'required|string|max:255',
            'pos'              => 'nullable|string|in:a,adv,n,num,p,pron,v',
            'definisi'         => 'required|string',
            'audio'            => 'nullable|file|mimes:mp3,wav,ogg,m4a,webm,mp4,mpeg,x-wav,wave|max:10240',
            'catatan_validasi' => 'nullable|string|max:1000',
            'contoh'           => 'nullable|array|max:20',
            'contoh.*.kalimat' => 'required|string|max:1000',
            'contoh.*.arti'    => 'nullable|string|max:1000',
        ], [
            'kata.required'             => 'Kata wajib diisi.',
            'kata.max'                  => 'Kata maksimal 255 karakter.',
            'pos.in'                    => 'Kelas kata tidak valid.',
            'definisi.required'         => 'Definisi wajib diisi.',
            'audio.file'                => 'File audio tidak valid.',
            'audio.mimes'               => 'Format audio harus MP3, WAV, OGG, M4A, atau WEBM.',
            'audio.max'                 => 'Ukuran file audio maksimal 10MB.',
            'catatan_validasi.max'      => 'Catatan validasi maksimal 1000 karakter.',
            'contoh.*.kalimat.required' => 'Contoh kalimat wajib diisi.',
        ]);

        DB::beginTransaction();
        try {
            $hasValidationPermission = auth()->user()->can('validasi kamus');
            $status = $hasValidationPermission ? 1 : 3;

            $data = $request->only(['kata', 'pos', 'definisi', 'catatan_validasi']);
            $data['status']     = $status;
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            if ($request->hasFile('audio')) {
                $data['audio'] = $request->file('audio')->store('kamus-audio', 'public');
            }

            $kamus = Kamus::create($data);

            if ($request->filled('contoh') && is_array($request->contoh)) {
                foreach ($request->contoh as $c) {
                    if (!empty(trim($c['kalimat'] ?? ''))) {
                        $kamus->contoh()->create([
                            'contoh_kalimat'      => $c['kalimat'],
                            'arti_contoh_kalimat' => $c['arti'] ?? '',
                        ]);
                    }
                }
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($kamus)
                ->withProperties([
                    'status'                   => $status == 1 ? 'langsung aktif' : 'menunggu validasi',
                    'has_validation_permission' => $hasValidationPermission,
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
        $hasValidationPermission = auth()->user()->can('validasi kamus');
        $isOwner = $kamus->created_by == auth()->id();

        if (!$hasValidationPermission && !$isOwner) {
            return back()->withErrors(['error' => 'Anda tidak memiliki izin untuk mengedit kamus ini.']);
        }

        $kamus->load('contoh');

        return Inertia::render('Kamus/Edit', [
            'kamus'                   => $kamus,
            'hasValidationPermission' => $hasValidationPermission,
        ]);
    }

    public function update(Request $request, Kamus $kamus)
    {
        $hasValidationPermission = auth()->user()->can('validasi kamus');
        $isOwner = $kamus->created_by == auth()->id();

        if (!$hasValidationPermission && !$isOwner) {
            return back()->withErrors(['error' => 'Anda tidak memiliki izin untuk mengedit kamus ini.']);
        }

        $dataToValidate = array_merge([
            'kata'             => $kamus->kata,
            'pos'              => $kamus->pos,
            'definisi'         => $kamus->definisi,
            'catatan_validasi' => $kamus->catatan_validasi,
        ], $request->only(['kata', 'pos', 'definisi', 'catatan_validasi']));

        validator($dataToValidate, [
            'kata'             => 'required|string|max:255',
            'pos'              => 'nullable|string|in:a,adv,n,num,p,pron,v',
            'definisi'         => 'required|string',
            'catatan_validasi' => 'nullable|string|max:1000',
        ])->validate();

        if ($request->hasFile('audio')) {
            $request->validate([
                'audio' => 'file|mimes:mp3,wav,ogg,m4a,webm,mp4,mpeg,x-wav,wave|max:10240',
            ]);
        }

        if ($request->has('contoh')) {
            $request->validate([
                'contoh'           => 'nullable|array|max:20',
                'contoh.*.kalimat' => 'required|string|max:1000',
                'contoh.*.arti'    => 'required|string|max:1000',
            ]);
        }

        DB::beginTransaction();
        try {
            if ($hasValidationPermission && $request->filled('status')) {
                $status = (int) $request->status;
                if (!in_array($status, [1, 2, 3, 4])) {
                    $status = $kamus->status;
                }
            } elseif (!$hasValidationPermission && $kamus->status == 1) {
                $status = 3;
            } elseif ($hasValidationPermission) {
                $status = 1;
            } else {
                $status = $kamus->status;
            }

            $data = [
                'kata'             => $request->kata             ?? $kamus->kata,
                'pos'              => $request->filled('pos') ? $request->pos : $kamus->pos,
                'definisi'         => $request->definisi         ?? $kamus->definisi,
                'catatan_validasi' => $request->catatan_validasi ?? $kamus->catatan_validasi,
                'status'           => $status,
                'updated_by'       => auth()->id(),
            ];

            if ($request->hasFile('audio')) {
                if ($kamus->audio) {
                    Storage::disk('public')->delete($kamus->audio);
                }
                $data['audio'] = $request->file('audio')->store('kamus-audio', 'public');
            } elseif ($request->input('remove_audio') == '1') {
                if ($kamus->audio) {
                    Storage::disk('public')->delete($kamus->audio);
                }
                $data['audio'] = null;
            }

            if (!$hasValidationPermission && in_array($kamus->status, [3, 4])) {
                KamusValidasi::where('kamus_id', $kamus->id)->delete();
                $data['status'] = 3;
            }

            $kamus->update($data);

            if ($request->has('contoh')) {
                $kamus->contoh()->delete();
                if (is_array($request->contoh)) {
                    foreach ($request->contoh as $c) {
                        if (!empty(trim($c['kalimat'] ?? ''))) {
                            $kamus->contoh()->create([
                                'contoh_kalimat'      => $c['kalimat'],
                                'arti_contoh_kalimat' => $c['arti'] ?? '',
                            ]);
                        }
                    }
                }
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($kamus)
                ->withProperties([
                    'old_status'               => $kamus->getOriginal('status'),
                    'new_status'               => $status,
                    'has_validation_permission' => $hasValidationPermission,
                    'is_owner'                 => $isOwner,
                ])
                ->log('Updated kamus entry');

            DB::commit();

            $message = $hasValidationPermission
                ? 'Kamus berhasil diupdate dan langsung aktif.'
                : 'Kamus berhasil diupdate, menunggu validasi admin.';

            return redirect()->route('kamus.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Update kamus error: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Gagal mengupdate kamus: ' . $e->getMessage()]);
        }
    }

    public function destroy(Kamus $kamus)
    {
        $hasFinalizePermission = auth()->user()->can('finalisasi kamus');
        $isOwner = $kamus->created_by == auth()->id();

        if (!$hasFinalizePermission && !$isOwner) {
            return back()->withErrors(['error' => 'Anda tidak memiliki izin untuk menghapus kamus ini.']);
        }

        DB::beginTransaction();
        try {
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

    public function tinjauan(Kamus $kamus)
    {
        $user = auth()->user();
        $hasValidationPermission = $user->can('validasi kamus');
        $hasFinalizePermission = $user->can('finalisasi kamus');

        if (!$hasValidationPermission && !$hasFinalizePermission) {
            abort(403, 'Anda tidak memiliki izin untuk meninjau kamus.');
        }

        $kamus->load(['creator', 'updater', 'validasiRecords.user', 'contoh']);

        $validasiDisetujui = $kamus->validasiRecords->where('action', 'setuju');
        $validasiDitolak   = $kamus->validasiRecords->where('action', 'tolak');

        $sudahValidasi = $kamus->validasiRecords->contains('user_id', $user->id);
        $validasiUser  = $kamus->validasiRecords->firstWhere('user_id', $user->id);

        $bisaValidasi  = $hasValidationPermission && $kamus->status == 3 && !$sudahValidasi;
        $bisaFinalisasi = $hasFinalizePermission && $kamus->status == 4;

        return Inertia::render('Kamus/Tinjauan', [
            'kamus'                   => $kamus,
            'validasiDisetujui'       => $validasiDisetujui->values(),
            'validasiDitolak'         => $validasiDitolak->values(),
            'validasiUser'            => $validasiUser,
            'sudahValidasi'           => $sudahValidasi,
            'bisaValidasi'            => $bisaValidasi,
            'bisaFinalisasi'          => $bisaFinalisasi,
            'hasValidationPermission' => $hasValidationPermission,
            'hasFinalizePermission'   => $hasFinalizePermission,
            'totalDisetujui'          => $validasiDisetujui->count(),
            'targetValidasi'          => 2,
        ]);
    }

    public function validasiKamus(Request $request, Kamus $kamus)
    {
        $request->validate([
            'action' => 'required|in:setuju,tolak',
            'catatan' => 'nullable|string|max:500',
        ]);

        if ($kamus->status != 3) {
            return back()->withErrors(['error' => 'Kamus ini tidak dalam status menunggu validasi.']);
        }

        $user = auth()->user();

        $sudahValidasi = KamusValidasi::where('kamus_id', $kamus->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($sudahValidasi) {
            return back()->withErrors(['error' => 'Anda sudah melakukan validasi pada kamus ini.']);
        }

        DB::beginTransaction();
        try {
            KamusValidasi::create([
                'kamus_id' => $kamus->id,
                'user_id'  => $user->id,
                'action'   => $request->action,
                'catatan'  => $request->catatan,
            ]);

            if ($request->action === 'tolak') {
                $kamus->update([
                    'status'     => 2,
                    'updated_by' => $user->id,
                ]);

                activity()
                    ->causedBy($user)
                    ->performedOn($kamus)
                    ->withProperties(['action' => 'tolak', 'catatan' => $request->catatan])
                    ->log('Kamus ditolak saat validasi');

                DB::commit();
                return redirect()->route('kamus.index')->with('success', 'Kamus berhasil ditolak.');
            }

            $totalDisetujui = KamusValidasi::where('kamus_id', $kamus->id)
                ->where('action', 'setuju')
                ->count();

            if ($totalDisetujui >= 2) {
                $kamus->update([
                    'status'     => 4,
                    'updated_by' => $user->id,
                ]);

                activity()
                    ->causedBy($user)
                    ->performedOn($kamus)
                    ->withProperties(['action' => 'setuju', 'total_validasi' => $totalDisetujui])
                    ->log('Kamus naik ke menunggu finalisasi setelah 2 validasi');

                DB::commit();
                return redirect()->route('kamus.index')->with('success', 'Validasi berhasil! Kamus menunggu finalisasi.');
            }

            activity()
                ->causedBy($user)
                ->performedOn($kamus)
                ->withProperties(['action' => 'setuju', 'total_validasi' => $totalDisetujui])
                ->log('Kamus disetujui (validasi ' . $totalDisetujui . '/2)');

            DB::commit();
            return redirect()->route('kamus.index')->with('success', 'Validasi pertama berhasil! Menunggu 1 validasi lagi.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal melakukan validasi: ' . $e->getMessage()]);
        }
    }

    public function finalisasiKamus(Request $request, Kamus $kamus)
    {
        $request->validate([
            'action'  => 'required|in:publish,tolak',
            'catatan' => 'nullable|string|max:500',
        ]);

        if ($kamus->status != 4) {
            return back()->withErrors(['error' => 'Kamus ini tidak dalam status menunggu finalisasi.']);
        }

        DB::beginTransaction();
        try {
            $user = auth()->user();

            if ($request->action === 'publish') {
                $kamus->update([
                    'status'     => 1,
                    'updated_by' => $user->id,
                ]);

                activity()
                    ->causedBy($user)
                    ->performedOn($kamus)
                    ->withProperties(['action' => 'publish', 'catatan' => $request->catatan])
                    ->log('Kamus dipublish (finalisasi)');

                DB::commit();
                return redirect()->route('kamus.index')->with('success', 'Kamus berhasil dipublish!');
            }

            $kamus->update([
                'status'     => 2,
                'updated_by' => $user->id,
            ]);

            KamusValidasi::where('kamus_id', $kamus->id)->delete();

            activity()
                ->causedBy($user)
                ->performedOn($kamus)
                ->withProperties(['action' => 'tolak', 'catatan' => $request->catatan])
                ->log('Kamus ditolak saat finalisasi');

            DB::commit();
            return redirect()->route('kamus.index')->with('success', 'Kamus ditolak pada tahap finalisasi.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal melakukan finalisasi: ' . $e->getMessage()]);
        }
    }

    public function approve(Kamus $kamus)
    {
        DB::beginTransaction();
        try {
            $kamus->update([
                'status'     => 1,
                'updated_by' => auth()->id(),
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
                'status'     => 2,
                'updated_by' => auth()->id(),
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
