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

        // Filter berdasarkan huruf
        if ($request->filled('letter')) {
            $query->where('bahasa_melayu', 'LIKE', $request->letter . '%');
        }

        // Sorting alfabetis
        $query->orderBy('bahasa_melayu', 'asc');

        $kamus = $query->paginate(10)->appends($request->query());

        // Hitung jumlah kata per huruf untuk badge
        $letterCounts = Kamus::where('status', 1)
            ->selectRaw('UPPER(LEFT(bahasa_melayu, 1)) as letter, COUNT(*) as count')
            ->groupBy('letter')
            ->pluck('count', 'letter')
            ->toArray();

        return Inertia::render('Frontend/Kamus', [
            'kamus' => $kamus,
            'search' => $request->search,
            'letter' => $request->letter,
            'letterCounts' => $letterCounts,
            'locale' => app()->getLocale(),
        ]);
    }
}