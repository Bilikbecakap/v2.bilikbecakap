<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use App\Models\MasterArtikel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    /**
     * Display a listing of the blog posts.
     */
    public function daftar(Request $request)
    {
        $query = Artikel::with(['kategori', 'creator'])
        ->select([
            'id', 'judul_indonesia', 'judul_melayu', 'judul_english',
            'slug', 'gambar_thumbnail', 'views_count', 'kategori_id',
            'tanggal_publish', 'created_at',
            'konten_indonesia',
            'konten_melayu',
            'konten_english'
        ])->where('status', 'published');

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
                    $query->orderBy('views_count', $direction);
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

        $artikelPaginated = $query->paginate(3)->appends($request->query());
        $locale = app()->getLocale();

        // Transform artikel items dengan multilingual support
        $artikel = $artikelPaginated->through(fn($item) => $this->transformArtikel($item, $locale));

        // Get populer artikel (top 3 by views) - tetap sama di semua halaman
        $populerArtikelRaw = Artikel::with('kategori')
            ->where('status', 'published')
            ->orderBy('views_count', 'desc')
            ->limit(3)
            ->get();

        $populerArtikel = $populerArtikelRaw->map(fn($item) => $this->transformArtikel($item, $locale));

        // Get kategori untuk filter
        $kategoriList = MasterArtikel::where('is_active', true)
            ->orderBy('nama_kategori')
            ->get();

        return Inertia::render('Frontend/Blog', [
            'artikel' => $artikel,
            'populerArtikel' => $populerArtikel,
            'kategoriList' => $kategoriList,
            'search' => $request->search,
            'kategori' => $request->kategori,
            'sort' => $request->sort,
            'direction' => $request->direction,
            'locale' => $locale,
        ]);
    }

    /**
     * Display the specified blog post.
     */
    public function baca($slug)
    {
        $artikel = Artikel::with(['kategori', 'creator', 'updater'])
            ->select([
                'id', 'judul_indonesia', 'judul_melayu', 'judul_english',
                'slug', 'konten_indonesia', 'konten_melayu', 'konten_english',
                'gambar_thumbnail', 'views_count', 'kategori_id',
                'tanggal_publish', 'created_at', 'created_by', 'status',
                'is_featured', 'meta_keywords'
            ])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // Increment views
        $artikel->incrementViews();

        $locale = app()->getLocale();

        // Transform artikel untuk menampilkan sesuai bahasa yang dipilih
        $artikelTransformed = $this->transformArtikel($artikel, $locale);

        $otherArticles = Artikel::with('kategori')
            ->select([
                'id', 'judul_indonesia', 'judul_melayu', 'judul_english', 'slug', 'gambar_thumbnail',
                'kategori_id', 'views_count', 'tanggal_publish', 'konten_indonesia',
                'konten_melayu', 'konten_english'
            ])
            ->where('id', '!=', $artikel->id)
            ->where('status', 'published')
            ->orderBy('views_count', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($item) => $this->transformArtikel($item, $locale));

        // Get all active categories
        $kategoriList = MasterArtikel::where('is_active', true)
            ->orderBy('nama_kategori')
            ->get();

        return Inertia::render('Frontend/BlogDetail', [
            'artikel' => $artikelTransformed,
            'otherArticles' => $otherArticles,
            'kategoriList' => $kategoriList,
            'locale' => $locale,
        ]);
    }

    /**
     * Transform artikel berdasarkan bahasa yang dipilih
     */
    private function transformArtikel($artikel, $locale)
    {
        $judul = match($locale) {
            'id' => $artikel->judul_indonesia ?? $artikel->judul_melayu ?? $artikel->judul_english,
            'ms' => $artikel->judul_melayu ?? $artikel->judul_indonesia ?? $artikel->judul_english,
            'en' => $artikel->judul_english ?? $artikel->judul_indonesia ?? $artikel->judul_melayu,
            default => $artikel->judul_indonesia
        };

        $konten = match($locale) {
            'id' => $artikel->konten_indonesia ?? $artikel->konten_melayu ?? $artikel->konten_english,
            'ms' => $artikel->konten_melayu ?? $artikel->konten_indonesia ?? $artikel->konten_english,
            'en' => $artikel->konten_english ?? $artikel->konten_indonesia ?? $artikel->konten_melayu,
            default => $artikel->konten_indonesia
        };

        return [
            'id' => $artikel->id,
            'slug' => $artikel->slug,
            'judul' => $judul,
            'konten' => $konten,
            'gambar_thumbnail' => $artikel->gambar_thumbnail,
            'views_count' => $artikel->views_count,
            'kategori_id' => $artikel->kategori_id,
            'kategori' => $artikel->kategori,
            'tanggal_publish' => $artikel->tanggal_publish,
            'created_at' => $artikel->created_at,
            'created_by' => $artikel->created_by ?? null,
            'creator' => $artikel->creator ?? null,
            'updater' => $artikel->updater ?? null,
            'status' => $artikel->status,
            'is_featured' => $artikel->is_featured ?? null,
            'meta_keywords' => $artikel->meta_keywords ?? null,
        ];
    }
}