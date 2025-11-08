<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Kamus;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KamusController extends Controller
{
    public function index(Request $request)
    {
        $query = Kamus::where('status', 1);

        // Filter berdasarkan search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('bahasa_melayu', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('bahasa_indonesia', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('keterangan', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        // Sorting alfabetis
        $query->orderBy('bahasa_melayu', 'asc');

        $kamus = $query->paginate(10)->appends($request->query());

        return Inertia::render('Frontend/Kamus', [
            'kamus' => $kamus,
            'search' => $request->search,
            'locale' => app()->getLocale(),
        ]);
    }
}