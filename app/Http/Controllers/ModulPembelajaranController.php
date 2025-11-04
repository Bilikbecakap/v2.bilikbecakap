<?php

namespace App\Http\Controllers;

use App\Models\ModulPembelajaran;
use App\Models\MasterModul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class ModulPembelajaranController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view modul pembelajaran', only: ['index']),
            new Middleware('permission:create modul pembelajaran', only: ['create', 'store']),
            new Middleware('permission:edit modul pembelajaran', only: ['edit', 'update']),
            new Middleware('permission:delete modul pembelajaran', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = ModulPembelajaran::with(['category', 'creator', 'updater']);

        // Filter berdasarkan search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan kategori
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        // Sorting
        if ($request->has('sort')) {
            $direction = $request->direction === 'asc' ? 'asc' : 'desc';
            switch ($request->sort) {
                case 'title':
                    $query->orderBy('title', $direction);
                    break;
                case 'views':
                    $query->orderBy('view_count', $direction);
                    break;
                case 'created_at':
                    $query->orderBy('created_at', $direction);
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $modul = $query->paginate(15)->appends($request->query());

        // Get kategori untuk filter
        $categoryList = MasterModul::where('is_active', true)->orderBy('nama_kategori')->get();

        return Inertia::render('ModulPembelajaran/Index', [
            'modul' => $modul,
            'categoryList' => $categoryList,
            'search' => $request->search,
            'status' => $request->status,
            'category' => $request->category,
            'sort' => $request->sort,
            'direction' => $request->direction,
        ]);
    }

    public function create()
    {
        $categoryList = MasterModul::where('is_active', true)->orderBy('urutan')->get();

        return Inertia::render('ModulPembelajaran/Create', [
            'categoryList' => $categoryList
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:master_modul,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:modul_pembelajaran,slug',
            'deskripsi' => 'nullable|string',
            'content' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'pdf_file' => 'nullable|mimes:pdf|max:102400', // 100MB max
            'video_embed' => 'nullable|url',
            'status' => 'required|in:draft,published,archived',
            'tanggal_publish' => 'nullable|date',
        ], [
            'category_id.required' => 'Kategori modul wajib dipilih.',
            'category_id.exists' => 'Kategori modul tidak valid.',
            'title.required' => 'Judul modul wajib diisi.',
            'thumbnail.image' => 'File thumbnail harus berupa gambar.',
            'thumbnail.mimes' => 'Format gambar harus JPEG, PNG, JPG, GIF, atau WEBP.',
            'thumbnail.max' => 'Ukuran gambar maksimal 2MB.',
            'pdf_file.mimes' => 'File harus berformat PDF.',
            'pdf_file.max' => 'Ukuran file PDF maksimal 100MB.',
            'video_embed.url' => 'URL video harus valid.',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->except(['thumbnail', 'pdf_file']);
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            // Handle thumbnail upload
            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = $request->file('thumbnail')->store('modul-thumbnails', 'public');
                $data['thumbnail'] = $thumbnailPath;
            }

            // Handle PDF upload
            if ($request->hasFile('pdf_file')) {
                $pdfPath = $request->file('pdf_file')->store('modul-pdfs', 'public');
                $data['pdf_file'] = $pdfPath;
            }

            $modul = ModulPembelajaran::create($data);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($modul)
                ->log('Created new modul pembelajaran');

            DB::commit();

            return redirect()->route('modul-pembelajaran.index')->with('success', 'Modul pembelajaran berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal membuat modul pembelajaran: ' . $e->getMessage()]);
        }
    }

    public function show(ModulPembelajaran $modulPembelajaran)
    {
        $modulPembelajaran->load(['category', 'creator', 'updater']);
        
        // Increment views count
        $modulPembelajaran->incrementViews();
        
        return Inertia::render('ModulPembelajaran/Show', [
            'modul' => $modulPembelajaran
        ]);
    }

    public function edit(ModulPembelajaran $modulPembelajaran)
    {
        $categoryList = MasterModul::where('is_active', true)->orderBy('urutan')->get();

        return Inertia::render('ModulPembelajaran/Edit', [
            'modul' => $modulPembelajaran,
            'categoryList' => $categoryList
        ]);
    }

    public function update(Request $request, ModulPembelajaran $modulPembelajaran)
    {
        $request->validate([
            'category_id' => 'required|exists:master_modul,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:modul_pembelajaran,slug,' . $modulPembelajaran->id,
            'deskripsi' => 'nullable|string',
            'content' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'pdf_file' => 'nullable|mimes:pdf|max:102400',
            'video_embed' => 'nullable|url',
            'status' => 'required|in:draft,published,archived',
            'tanggal_publish' => 'nullable|date',
            'remove_thumbnail' => 'nullable|string',
            'remove_pdf' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->except(['thumbnail', 'pdf_file', 'remove_thumbnail', 'remove_pdf']);
            $data['updated_by'] = auth()->id();

            // Handle thumbnail upload/removal
            if ($request->has('remove_thumbnail') && $request->remove_thumbnail == '1') {
                if ($modulPembelajaran->thumbnail) {
                    Storage::disk('public')->delete($modulPembelajaran->thumbnail);
                }
                $data['thumbnail'] = null;
            } elseif ($request->hasFile('thumbnail')) {
                if ($modulPembelajaran->thumbnail) {
                    Storage::disk('public')->delete($modulPembelajaran->thumbnail);
                }
                $thumbnailPath = $request->file('thumbnail')->store('modul-thumbnails', 'public');
                $data['thumbnail'] = $thumbnailPath;
            }

            // Handle PDF upload/removal
            if ($request->has('remove_pdf') && $request->remove_pdf == '1') {
                if ($modulPembelajaran->pdf_file) {
                    Storage::disk('public')->delete($modulPembelajaran->pdf_file);
                }
                $data['pdf_file'] = null;
            } elseif ($request->hasFile('pdf_file')) {
                if ($modulPembelajaran->pdf_file) {
                    Storage::disk('public')->delete($modulPembelajaran->pdf_file);
                }
                $pdfPath = $request->file('pdf_file')->store('modul-pdfs', 'public');
                $data['pdf_file'] = $pdfPath;
            }

            $modulPembelajaran->update($data);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($modulPembelajaran)
                ->log('Updated modul pembelajaran');

            DB::commit();

            return redirect()->route('modul-pembelajaran.index')->with('success', 'Modul pembelajaran berhasil diupdate.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal mengupdate modul pembelajaran: ' . $e->getMessage()]);
        }
    }

    public function destroy(ModulPembelajaran $modulPembelajaran)
    {
        DB::beginTransaction();
        try {
            // Delete files if exist
            if ($modulPembelajaran->thumbnail) {
                Storage::disk('public')->delete($modulPembelajaran->thumbnail);
            }
            
            if ($modulPembelajaran->pdf_file) {
                Storage::disk('public')->delete($modulPembelajaran->pdf_file);
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($modulPembelajaran)
                ->withProperties(['deleted_modul' => $modulPembelajaran->toArray()])
                ->log('Deleted modul pembelajaran');

            $modulPembelajaran->delete();

            DB::commit();

            return redirect()->route('modul-pembelajaran.index')->with('success', 'Modul pembelajaran berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menghapus modul pembelajaran: ' . $e->getMessage()]);
        }
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'upload' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        $path = $request->file('upload')->store('modul-images', 'public');
        
        return response()->json([
            'url' => "/storage/{$path}"
        ]);
    }
}