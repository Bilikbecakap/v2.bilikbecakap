<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\MasterArtikel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class ArtikelController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view artikel', only: ['index']),
            new Middleware('permission:create artikel', only: ['create', 'store']),
            new Middleware('permission:edit artikel', only: ['edit', 'update']),
            new Middleware('permission:delete artikel', only: ['destroy']),
            new Middleware('permission:approve artikel', only: ['approve', 'reject', 'bulkApprove', 'bulkReject']),
        ];
    }

    public function index(Request $request)
    {
        $query = Artikel::with(['kategori', 'creator', 'updater']);

        // Filter berdasarkan search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan kategori
        if ($request->filled('kategori')) {
            $query->byKategori($request->kategori);
        }

        // Filter berdasarkan featured
        if ($request->filled('featured')) {
            $query->featured();
        }

        // Sorting
        if ($request->has('sort') && $request->sort === 'created_at') {
            $direction = $request->direction === 'asc' ? 'asc' : 'desc';
            $query->orderBy('created_at', $direction);
        } elseif ($request->has('sort') && $request->sort === 'views') {
            $direction = $request->direction === 'asc' ? 'asc' : 'desc';
            $query->orderBy('views_count', $direction);
        } else {
            // Default sorting - prioritas status pending, kemudian terbaru
            $query->orderByRaw('CASE WHEN status = "pending" THEN 0 ELSE 1 END')
                ->orderBy('created_at', 'desc');
        }

        $artikel = $query->paginate(15)->appends($request->query());

        // Tambahkan informasi permission dan ownership
        $currentUserId = auth()->id();
        $hasApprovalPermission = auth()->user()->can('approve artikel');

        // Transform data untuk menambahkan informasi ownership
        $artikelData = $artikel->getCollection()->map(function ($item) use ($currentUserId, $hasApprovalPermission) {
            $item->can_edit = $hasApprovalPermission || $item->created_by == $currentUserId;
            $item->can_delete = $hasApprovalPermission || $item->created_by == $currentUserId;
            $item->can_approve = $hasApprovalPermission && $item->status == 'pending';
            return $item;
        });

        $artikel->setCollection($artikelData);

        // Get kategori untuk filter
        $kategoriList = MasterArtikel::where('is_active', true)->orderBy('nama_kategori')->get();

        return Inertia::render('Artikel/Index', [
            'artikel' => $artikel,
            'kategoriList' => $kategoriList,
            'search' => $request->search,
            'status' => $request->status,
            'kategori' => $request->kategori,
            'featured' => $request->featured,
            'sort' => $request->sort,
            'direction' => $request->direction,
            'hasApprovalPermission' => $hasApprovalPermission,
            'currentUserId' => $currentUserId
        ]);
    }

    public function create()
    {
        $kategoriList = MasterArtikel::where('is_active', true)->orderBy('urutan')->get();

        return Inertia::render('Artikel/Create', [
            'kategoriList' => $kategoriList
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul_indonesia' => 'nullable|string|max:255',
            'judul_melayu' => 'nullable|string|max:255',
            'judul_english' => 'nullable|string|max:255',
            'konten_indonesia' => 'nullable|string',
            'konten_melayu' => 'nullable|string',
            'konten_english' => 'nullable|string',
            'slug' => 'nullable|string|unique:artikel,slug',
            'kategori_id' => 'required|exists:master_artikel,id',
            'gambar_thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'meta_keywords' => 'nullable|string',
            'status' => 'nullable|in:draft,pending,published',
            'is_featured' => 'nullable|boolean',
            'tanggal_publish' => 'nullable|date',
        ], [
            'kategori_id.required' => 'Kategori artikel wajib dipilih.',
            'kategori_id.exists' => 'Kategori artikel tidak valid.',
            'gambar_thumbnail.image' => 'File thumbnail harus berupa gambar.',
            'gambar_thumbnail.mimes' => 'Format gambar harus JPEG, PNG, JPG, GIF, atau WEBP.',
            'gambar_thumbnail.max' => 'Ukuran gambar maksimal 2MB.',
        ]);

        // Validasi minimal 1 judul harus diisi
        if (!$request->judul_indonesia && !$request->judul_melayu && !$request->judul_english) {
            return back()->withInput()->withErrors(['judul_indonesia' => 'Minimal satu judul harus diisi.']);
        }

        DB::beginTransaction();
        try {
            $hasApprovalPermission = auth()->user()->can('approve artikel');
            $status = $request->status ?: 'draft';

            // Jika user tidak punya permission approval, status tidak bisa langsung published
            if (!$hasApprovalPermission && $status == 'published') {
                $status = 'pending';
            }

            $data = $request->except('gambar_thumbnail');
            $data['status'] = $status;
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            // Handle thumbnail upload
            if ($request->hasFile('gambar_thumbnail')) {
                $thumbnailPath = $request->file('gambar_thumbnail')->store('artikel-thumbnails', 'public');
                $data['gambar_thumbnail'] = $thumbnailPath;
            }

            $artikel = Artikel::create($data);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($artikel)
                ->withProperties([
                    'status' => $status,
                    'has_approval_permission' => $hasApprovalPermission
                ])
                ->log('Created new artikel');

            DB::commit();

            $message = match($status) {
                'draft' => 'Artikel berhasil disimpan sebagai draft.',
                'pending' => 'Artikel berhasil dibuat, menunggu persetujuan admin.',
                'published' => 'Artikel berhasil dibuat dan dipublikasikan.',
                default => 'Artikel berhasil dibuat.'
            };

            return redirect('/admin/artikel')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal membuat artikel: ' . $e->getMessage()]);
        }
    }

    public function edit(Artikel $artikel)
    {
        // Cek permission
        $hasApprovalPermission = auth()->user()->can('approve artikel');
        $isOwner = $artikel->created_by == auth()->id();

        if (!$hasApprovalPermission && !$isOwner) {
            return back()->withErrors(['error' => 'Anda tidak memiliki izin untuk mengedit artikel ini.']);
        }

        $kategoriList = MasterArtikel::where('is_active', true)->orderBy('urutan')->get();

        // ADD: Kirim informasi permission ke frontend
        $artikel->can_edit = $hasApprovalPermission || $isOwner;
        $artikel->can_delete = $hasApprovalPermission || $isOwner;

        return Inertia::render('Artikel/Edit', [
            'artikel' => $artikel,
            'kategoriList' => $kategoriList,
            'hasApprovalPermission' => $hasApprovalPermission,  // ADD
            'currentUserId' => auth()->id()  // ADD
        ]);
    }

    private function hasContentChanges(Request $request, Artikel $artikel)
    {
        $contentFields = [
            'judul_indonesia', 'judul_melayu', 'judul_english',
            'konten_indonesia', 'konten_melayu', 'konten_english'
        ];
        
        foreach ($contentFields as $field) {
            if ($request->get($field) != $artikel->$field) {
                return true;
            }
        }
        
        return $request->hasFile('gambar_thumbnail');
    }

    public function update(Request $request, Artikel $artikel)
    {
        $hasApprovalPermission = auth()->user()->can('approve artikel');
        $isOwner = $artikel->created_by == auth()->id();

        if (!$hasApprovalPermission && !$isOwner) {
            return back()->withErrors(['error' => 'Anda tidak memiliki izin untuk mengedit artikel ini.']);
        }

        $request->validate([
            'judul_indonesia' => 'nullable|string|max:255',
            'judul_melayu' => 'nullable|string|max:255',
            'judul_english' => 'nullable|string|max:255',
            'konten_indonesia' => 'nullable|string',
            'konten_melayu' => 'nullable|string',
            'konten_english' => 'nullable|string',
            'slug' => 'nullable|string|unique:artikel,slug,' . $artikel->id,
            'kategori_id' => 'nullable|exists:master_artikel,id',
            'gambar_thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'meta_keywords' => 'nullable|string',
            'status' => 'in:draft,pending,published,archived',
            'is_featured' => 'boolean',
            'tanggal_publish' => 'nullable|date',
            'remove_thumbnail' => 'nullable|string', // ADD: validation untuk flag remove
        ]);

        // Validasi minimal 1 judul harus diisi
        if (empty($artikel->judul_indonesia) && empty($artikel->judul_melayu) && empty($artikel->judul_english) && 
            !$request->judul_indonesia && !$request->judul_melayu && !$request->judul_english) {
            return back()->withInput()->withErrors(['judul_indonesia' => 'Minimal satu judul harus diisi.']);
        }

        DB::beginTransaction();
        try {
            $status = $request->status ?: $artikel->status;

            // Jika user biasa (bukan admin) mengubah artikel yang sudah published, 
            // kembali ke pending untuk review ulang
            if (!$hasApprovalPermission && 
                $artikel->status == 'published' && 
                $this->hasContentChanges($request, $artikel)) {
                $status = 'pending';
            }
            
            // Jika user tidak punya permission approval, status tidak bisa langsung published
            // (kecuali artikel memang sudah published dan tidak ada perubahan konten)
            if (!$hasApprovalPermission && 
                $status == 'published' && 
                $artikel->status != 'published') {
                $status = 'pending';
            }

            $data = $request->except(['gambar_thumbnail', 'remove_thumbnail']); // UBAH: exclude remove_thumbnail juga
            $data['status'] = $status;
            $data['updated_by'] = auth()->id();

            // UBAH: Handle thumbnail upload/removal dengan logic baru
            if ($request->has('remove_thumbnail') && $request->remove_thumbnail == '1') {
                // User wants to remove thumbnail
                if ($artikel->gambar_thumbnail) {
                    Storage::disk('public')->delete($artikel->gambar_thumbnail);
                }
                $data['gambar_thumbnail'] = null;
            } elseif ($request->hasFile('gambar_thumbnail')) {
                // User uploads new thumbnail
                if ($artikel->gambar_thumbnail) {
                    Storage::disk('public')->delete($artikel->gambar_thumbnail);
                }
                $thumbnailPath = $request->file('gambar_thumbnail')->store('artikel-thumbnails', 'public');
                $data['gambar_thumbnail'] = $thumbnailPath;
            }

            $artikel->update($data);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($artikel)
                ->withProperties([
                    'old_status' => $artikel->getOriginal('status'),
                    'new_status' => $status,
                    'has_approval_permission' => $hasApprovalPermission,
                    'is_owner' => $isOwner
                ])
                ->log('Updated artikel');

            DB::commit();

            $message = match($status) {
                'draft' => 'Artikel berhasil diupdate sebagai draft.',
                'pending' => $artikel->getOriginal('status') == 'published' ? 
                            'Artikel berhasil diupdate. Karena ada perubahan konten, artikel perlu review ulang oleh admin.' : 
                            'Artikel berhasil diupdate, menunggu persetujuan admin.',
                'published' => 'Artikel berhasil diupdate dan dipublikasikan.',
                'archived' => 'Artikel berhasil diarsipkan.',
                default => 'Artikel berhasil diupdate.'
            };

            return redirect('/admin/artikel')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal mengupdate artikel: ' . $e->getMessage()]);
        }
    }

    public function destroy(Artikel $artikel)
    {
        DB::beginTransaction();
        try {
            $hasApprovalPermission = auth()->user()->can('approve artikel');
            $isOwner = $artikel->created_by == auth()->id();

            // Cek permission
            if (!$hasApprovalPermission && !$isOwner) {
                return back()->withErrors(['error' => 'Anda tidak memiliki izin untuk menghapus artikel ini.']);
            }

            // Delete thumbnail if exists
            if ($artikel->gambar_thumbnail) {
                Storage::disk('public')->delete($artikel->gambar_thumbnail);
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($artikel)
                ->withProperties([
                    'deleted_artikel' => $artikel->toArray(),
                    'has_approval_permission' => $hasApprovalPermission,
                    'is_owner' => $isOwner
                ])
                ->log('Deleted artikel');

            $artikel->delete();

            DB::commit();

            return redirect('/admin/artikel')->with('success', 'Artikel berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menghapus artikel: ' . $e->getMessage()]);
        }
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'upload' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        $path = $request->file('upload')->store('artikel-images', 'public');
        
        return response()->json([
            'url' => "/storage/{$path}"
        ]);
    }

    // Approval Methods
    public function approve(Artikel $artikel)
    {
        DB::beginTransaction();
        try {
            $artikel->update([
                'status' => 'published',
                'tanggal_publish' => now(),
                'updated_by' => auth()->id(),
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($artikel)
                ->log('Approved artikel');

            DB::commit();

            return back()->with('success', 'Artikel berhasil disetujui dan dipublikasikan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyetujui artikel: ' . $e->getMessage()]);
        }
    }

    public function reject(Artikel $artikel)
    {
        DB::beginTransaction();
        try {
            $artikel->update([
                'status' => 'draft',
                'updated_by' => auth()->id(),
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($artikel)
                ->log('Rejected artikel');

            DB::commit();

            return back()->with('success', 'Artikel berhasil ditolak, dikembalikan ke draft.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menolak artikel: ' . $e->getMessage()]);
        }
    }

    public function show(Artikel $artikel)
    {
        // Load relasi
        $artikel->load(['kategori', 'creator', 'updater']);
        
        // Increment views count
        $artikel->incrementViews();
        
        return Inertia::render('Artikel/Show', [
            'artikel' => $artikel
        ]);
    }

}