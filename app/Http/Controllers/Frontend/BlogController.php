<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use App\Models\MasterArtikel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BlogController extends Controller
{
    /**
     * Display a listing of the blog posts.
     */
    public function index(Request $request)
    {
        $query = Artikel::with(['kategori', 'creator'])
            ->select([
                'id', 'judul_indonesia', 'judul_melayu', 'judul_english',
                'slug', 'gambar_thumbnail', 'view_count', 'kategori_id',
                'tanggal_publish', 'created_at'
            ])
            ->where('status', 'published');

        // Filter berdasarkan search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter berdasarkan kategori
        if ($request->filled('kategori')) {
            $query->byKategori($request->kategori);
        }

        // Sorting
        if ($request->has('sort')) {
            $direction = $request->direction === 'asc' ? 'asc' : 'desc';
            switch ($request->sort) {
                case 'judul':
                    $query->orderBy('judul_indonesia', $direction);
                    break;
                case 'views':
                    $query->orderBy('view_count', $direction);
                    break;
                case 'tanggal':
                    $query->orderBy('tanggal_publish', $direction);
                    break;
                default:
                    $query->orderBy('tanggal_publish', 'desc');
            }
        } else {
            $query->orderBy('tanggal_publish', 'desc');
        }

        $artikel = $query->paginate(6)->appends($request->query());

        // Get kategori untuk filter
        $kategoriList = MasterArtikel::where('is_active', true)
            ->orderBy('nama_kategori')
            ->get();

        return Inertia::render('Frontend/Blog', [
            'artikel' => $artikel,
            'kategoriList' => $kategoriList,
            'search' => $request->search,
            'kategori' => $request->kategori,
            'sort' => $request->sort,
            'direction' => $request->direction,
        ]);
    }

    /**
     * Display the specified blog post.
     */
    public function show($slug)
    {
        $artikel = Artikel::with(['kategori', 'creator', 'updater'])
            ->select([
                'id', 'judul_indonesia', 'judul_melayu', 'judul_english',
                'slug', 'konten_indonesia', 'konten_melayu', 'konten_english',
                'gambar_thumbnail', 'view_count', 'kategori_id',
                'tanggal_publish', 'created_at', 'created_by'
            ])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // Increment views
        $artikel->incrementViews();

        // 5 artikel lain secara acak (bukan yang ini)
        $otherArticles = Artikel::with('kategori')
            ->select([
                'id', 'judul_indonesia', 'slug', 'gambar_thumbnail',
                'kategori_id', 'view_count', 'tanggal_publish'
            ])
            ->where('id', '!=', $artikel->id)
            ->where('status', 'published')
            ->orderBy('view_count', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Frontend/BlogDetail', [
            'artikel' => $artikel,
            'otherArticles' => $otherArticles
        ]);
    }
}