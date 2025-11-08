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
            new Middleware('permission:delete kamus', only: ['destroy', 'bulkDelete']),
            new Middleware('permission:validasi kamus', only: ['validate', 'approve', 'reject', 'bulkApprove', 'bulkReject']),
        ];
    }

    public function index(Request $request)
    {
        $query = Kamus::with(['creator', 'updater']);

        // Filter berdasarkan search (bahasa_melayu atau bahasa_indonesia)
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('bahasa_melayu', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('bahasa_indonesia', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('keterangan', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        // Filter berdasarkan status - menggunakan filled() untuk memastikan nilai tidak kosong
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sorting berdasarkan parameter
        if ($request->has('sort') && $request->sort === 'created_at') {
            $direction = $request->direction === 'asc' ? 'asc' : 'desc';
            $query->orderBy('created_at', $direction);
        } else {
            // Default sorting - prioritas status menunggu, kemudian alfabetis
            $query->orderByRaw('CASE WHEN status = 3 THEN 0 ELSE 1 END')
                ->orderBy('bahasa_melayu', 'asc');
        }

        $kamus = $query->paginate(15)->appends($request->query());

        // Tambahkan informasi permission dan ownership
        $currentUserId = auth()->id();
        $hasValidationPermission = auth()->user()->can('validasi kamus');

        // Transform data untuk menambahkan informasi ownership
        $kamusData = $kamus->getCollection()->map(function ($item) use ($currentUserId, $hasValidationPermission) {
            $item->can_edit = $hasValidationPermission || $item->create_by == $currentUserId;
            $item->can_delete = $hasValidationPermission || $item->create_by == $currentUserId;
            return $item;
        });

        $kamus->setCollection($kamusData);

        return Inertia::render('Kamus/Index', [
            'kamus' => $kamus,
            'search' => $request->search,
            'status' => $request->status,
            'sort' => $request->sort,
            'direction' => $request->direction,
            'hasValidationPermission' => $hasValidationPermission,
            'currentUserId' => $currentUserId
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
            'audio' => 'nullable|file|mimes:mp3,wav,ogg,m4a,webm,mp4,mpeg,x-wav,wave|max:10240',
            'keterangan' => 'nullable|string|max:1000',
        ], [
            'bahasa_melayu.required' => 'Bahasa Melayu wajib diisi.',
            'bahasa_melayu.max' => 'Bahasa Melayu maksimal 255 karakter.',
            'bahasa_indonesia.required' => 'Bahasa Indonesia wajib diisi.',
            'bahasa_indonesia.max' => 'Bahasa Indonesia maksimal 255 karakter.',
            'audio.file' => 'File audio tidak valid.',
            'audio.mimes' => 'Format audio harus MP3, WAV, OGG, M4A, atau WEBM.',
            'audio.max' => 'Ukuran file audio maksimal 10MB.',
            'keterangan.max' => 'Keterangan maksimal 1000 karakter.',
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
        // Cek apakah user bisa mengedit data ini
        $hasValidationPermission = auth()->user()->can('validasi kamus');
        $isOwner = $kamus->create_by == auth()->id();

        if (!$hasValidationPermission && !$isOwner) {
            return back()->withErrors(['error' => 'Anda tidak memiliki izin untuk mengedit kamus ini.']);
        }

        return Inertia::render('Kamus/Edit', [
            'kamus' => $kamus
        ]);
    }

    public function update(Request $request, Kamus $kamus)
    {
        // Cek permission
        $hasValidationPermission = auth()->user()->can('validasi kamus');
        $isOwner = $kamus->create_by == auth()->id();

        if (!$hasValidationPermission && !$isOwner) {
            return back()->withErrors(['error' => 'Anda tidak memiliki izin untuk mengedit kamus ini.']);
        }

        // Merge data lama dengan data baru
        $dataToValidate = array_merge([
            'bahasa_melayu' => $kamus->bahasa_melayu,
            'bahasa_indonesia' => $kamus->bahasa_indonesia,
            'keterangan' => $kamus->keterangan,
        ], $request->only(['bahasa_melayu', 'bahasa_indonesia', 'keterangan']));

        $validated = validator($dataToValidate, [
            'bahasa_melayu' => 'required|string|max:255',
            'bahasa_indonesia' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:1000',
        ])->validate();

        // Validasi audio
        if ($request->hasFile('audio')) {
            $request->validate([
                'audio' => 'file|mimes:mp3,wav,ogg,m4a,webm,mp4,mpeg,x-wav,wave|max:10240',
            ]);
        }

        DB::beginTransaction();
        try {
            if (!$hasValidationPermission && $kamus->status == 1) {
                $status = 3;
            } elseif ($hasValidationPermission) {
                $status = 1;
            } else {
                $status = $kamus->status;
            }

            $data = [
                'bahasa_melayu' => $request->bahasa_melayu ?? $kamus->bahasa_melayu,
                'bahasa_indonesia' => $request->bahasa_indonesia ?? $kamus->bahasa_indonesia,
                'keterangan' => $request->keterangan ?? $kamus->keterangan,
                'status' => $status,
                'update_by' => auth()->id(),
            ];

            // Handle audio upload/removal
            if ($request->hasFile('audio')) {
                // Remove old audio if exists
                if ($kamus->audio) {
                    Storage::disk('public')->delete($kamus->audio);
                }
                // Upload new audio
                $audioPath = $request->file('audio')->store('kamus-audio', 'public');
                $data['audio'] = $audioPath;
            } elseif ($request->input('remove_audio') == '1') {
                // Remove existing audio if flag is set
                if ($kamus->audio) {
                    Storage::disk('public')->delete($kamus->audio);
                }
                $data['audio'] = null;
            }

            $kamus->update($data);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($kamus)
                ->withProperties([
                    'old_status' => $kamus->getOriginal('status'),
                    'new_status' => $status,
                    'has_validation_permission' => $hasValidationPermission,
                    'is_owner' => $isOwner
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
        DB::beginTransaction();
        try {
            $hasValidationPermission = auth()->user()->can('validasi kamus');
            $isOwner = $kamus->create_by == auth()->id();

            // Cek permission: harus punya validasi permission ATAU owner dari data
            if (!$hasValidationPermission && !$isOwner) {
                return back()->withErrors(['error' => 'Anda tidak memiliki izin untuk menghapus kamus ini.']);
            }

            // User biasa tidak bisa menghapus kamus yang sudah aktif (kecuali punya permission validasi)
            if (!$hasValidationPermission && $kamus->status == 1) {
                return back()->withErrors(['error' => 'Anda tidak dapat menghapus kamus yang sudah aktif.']);
            }

            if ($kamus->audio) {
                Storage::disk('public')->delete($kamus->audio);
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($kamus)
                ->withProperties([
                    'deleted_kamus' => $kamus->toArray(),
                    'has_validation_permission' => $hasValidationPermission,
                    'is_owner' => $isOwner
                ])
                ->log('Deleted kamus entry');

            $kamus->delete();

            DB::commit();

            return redirect()->route('kamus.index')->with('success', 'Kamus berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menghapus kamus: ' . $e->getMessage()]);
        }
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

    // BULK ACTION METHODS
    
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:kamus,id'
        ]);

        DB::beginTransaction();
        try {
            $currentUserId = auth()->id();
            $hasValidationPermission = auth()->user()->can('validasi kamus');

            // Get the kamus records with validation
            $kamusRecords = Kamus::whereIn('id', $request->ids)->get();
            
            $deletedCount = 0;
            $skippedCount = 0;
            $errors = [];

            foreach ($kamusRecords as $kamus) {
                $isOwner = $kamus->create_by == $currentUserId;
                
                // Check permissions
                if (!$hasValidationPermission && !$isOwner) {
                    $skippedCount++;
                    $errors[] = "Tidak memiliki izin untuk menghapus '{$kamus->bahasa_melayu}'";
                    continue;
                }

                // User biasa tidak bisa menghapus kamus yang sudah aktif
                if (!$hasValidationPermission && $kamus->status == 1) {
                    $skippedCount++;
                    $errors[] = "Tidak dapat menghapus '{$kamus->bahasa_melayu}' karena sudah aktif";
                    continue;
                }

                try {
                    // Delete audio file if exists
                    if ($kamus->audio) {
                        Storage::disk('public')->delete($kamus->audio);
                    }

                    activity()
                        ->causedBy(auth()->user())
                        ->performedOn($kamus)
                        ->withProperties([
                            'deleted_kamus' => $kamus->toArray(),
                            'bulk_action' => true,
                            'has_validation_permission' => $hasValidationPermission,
                            'is_owner' => $isOwner
                        ])
                        ->log('Bulk deleted kamus entry');

                    $kamus->delete();
                    $deletedCount++;

                } catch (\Exception $e) {
                    $skippedCount++;
                    $errors[] = "Gagal menghapus '{$kamus->bahasa_melayu}': " . $e->getMessage();
                }
            }

            DB::commit();

            // Prepare response message
            $message = "Berhasil menghapus {$deletedCount} kamus.";
            if ($skippedCount > 0) {
                $message .= " {$skippedCount} kamus dilewati.";
            }

            if (!empty($errors) && count($errors) <= 5) {
                $message .= " Error: " . implode(', ', $errors);
            } elseif (!empty($errors)) {
                $message .= " Ada " . count($errors) . " error saat menghapus.";
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menghapus kamus: ' . $e->getMessage()]);
        }
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:kamus,id'
        ]);

        DB::beginTransaction();
        try {
            // Get kamus records with status 3 (pending)
            $kamusRecords = Kamus::whereIn('id', $request->ids)
                ->where('status', 3)
                ->get();

            if ($kamusRecords->isEmpty()) {
                return back()->withErrors(['error' => 'Tidak ada kamus dengan status menunggu yang dipilih.']);
            }

            $approvedCount = 0;
            foreach ($kamusRecords as $kamus) {
                $kamus->update([
                    'status' => 1,
                    'update_by' => auth()->id(),
                ]);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($kamus)
                    ->withProperties(['bulk_action' => true])
                    ->log('Bulk approved kamus entry');

                $approvedCount++;
            }

            DB::commit();

            return back()->with('success', "Berhasil menyetujui {$approvedCount} kamus.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyetujui kamus: ' . $e->getMessage()]);
        }
    }

    public function bulkReject(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:kamus,id'
        ]);

        DB::beginTransaction();
        try {
            // Get kamus records with status 3 (pending)
            $kamusRecords = Kamus::whereIn('id', $request->ids)
                ->where('status', 3)
                ->get();

            if ($kamusRecords->isEmpty()) {
                return back()->withErrors(['error' => 'Tidak ada kamus dengan status menunggu yang dipilih.']);
            }

            $rejectedCount = 0;
            foreach ($kamusRecords as $kamus) {
                $kamus->update([
                    'status' => 2,
                    'update_by' => auth()->id(),
                ]);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($kamus)
                    ->withProperties(['bulk_action' => true])
                    ->log('Bulk rejected kamus entry');

                $rejectedCount++;
            }

            DB::commit();

            return back()->with('success', "Berhasil menolak {$rejectedCount} kamus.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menolak kamus: ' . $e->getMessage()]);
        }
    }
}