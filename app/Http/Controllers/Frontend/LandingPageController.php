<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Artikel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LandingPageController extends Controller
{
    public function index()
    {
        // Ambil 3 artikel terbaru yang published
        $latestArtikel = Artikel::with(['kategori', 'creator'])
            ->where('status', 'published')
            ->orderBy('tanggal_publish', 'desc')
            ->take(3)
            ->get()
            ->map(function ($artikel) {
                $locale = app()->getLocale();
                
                // Pilih konten sesuai bahasa
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

                // Strip HTML dan batasi 100 kata
                $plainText = strip_tags($konten);
                $excerpt = Str::words($plainText, 20, '...');

                return [
                    'id' => $artikel->id,
                    'slug' => $artikel->slug,
                    'judul' => $judul,
                    'excerpt' => $excerpt,
                    'gambar_thumbnail' => $artikel->gambar_thumbnail ? asset('storage/' . $artikel->gambar_thumbnail) : null,
                    'tanggal_publish' => $artikel->tanggal_publish,
                    'kategori' => $artikel->kategori->nama_kategori ?? null,
                    'penulis' => $artikel->creator->name ?? 'Admin',
                ];
            });

        return Inertia::render('Frontend/LandingPage', [
            'locale' => app()->getLocale(),
            'auth' => [
                'user' => Auth::user(),
            ],
            'latestArtikel' => $latestArtikel,
        ]);
    }
}