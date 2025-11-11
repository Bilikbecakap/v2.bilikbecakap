<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ModulPembelajaran;
use App\Models\MasterModul;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PembelajaranController extends Controller
{
    public function index(Request $request)
    {
        $query = ModulPembelajaran::with(['category', 'creator'])
            ->select([
                'id', 'title', 'slug', 'deskripsi', 'thumbnail', 'view_count',
                'tanggal_publish', 'category_id', 'created_at'
            ])
            ->where('status', 'published');

        // Filter berdasarkan search
        if ($request->filled('search')) {
            $query->search($request->search);
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
                case 'tanggal_publish':
                    $query->orderBy('tanggal_publish', $direction);
                    break;
                default:
                    $query->orderBy('tanggal_publish', 'desc');
            }
        } else {
            $query->orderBy('tanggal_publish', 'desc');
        }

        $modul = $query->paginate(12)->appends($request->query());

        // Get kategori untuk filter
        $categoryList = MasterModul::where('is_active', true)
            ->orderBy('nama_kategori')
            ->get();

        return Inertia::render('Frontend/Pembelajaran', [
            'modul' => $modul,
            'categoryList' => $categoryList,
            'search' => $request->search,
            'category' => $request->category,
            'sort' => $request->sort,
            'direction' => $request->direction,
        ]);
    }



    public function show($slug)
    {
        $modul = ModulPembelajaran::with(['category', 'creator'])
            ->select([
                'id', 'title', 'slug', 'deskripsi', 'content', 'thumbnail', 
                'pdf_file', 'video_embed', 'view_count', 'category_id', 
                'created_at', 'created_by'
            ])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $modul->incrementViews();

        $otherModules = ModulPembelajaran::with('category')
            ->select(['id', 'title', 'slug', 'thumbnail', 'category_id', 'view_count'])
            ->where('id', '!=', $modul->id)
            ->where('status', 'published')
            ->orderBy('view_count', 'desc') 
            ->limit(6)
            ->get();

        $komentars = $modul->komentars()->orderBy('created_at', 'desc')->get();

        // Ambil quiz yang sesuai dengan modul ini
        $relatedQuiz = \App\Models\Quizzes::where('modul_pembelajaran_id', $modul->id)
            ->where('type', 'modul')
            ->where('status', 'active')
            ->select(['id', 'title', 'slug', 'description', 'thumbnail', 'duration'])
            ->first();

        return Inertia::render('Frontend/PembelajaranDetail', [
            'modul' => $modul,
            'otherModules' => $otherModules,
            'komentars' => $komentars,
            'relatedQuiz' => $relatedQuiz,  // Tambah ini
        ]);
    }
}