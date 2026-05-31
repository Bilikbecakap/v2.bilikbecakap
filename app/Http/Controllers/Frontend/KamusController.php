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
        $query = Kamus::where('status', 1)->with('contoh');

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('kata', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('definisi', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        if ($request->filled('letter')) {
            $query->where('kata', 'LIKE', $request->letter . '%');
        }

        $query->orderBy('kata', 'asc');

        $kamus = $query->paginate(10)->appends($request->query());

        $letterCounts = Kamus::where('status', 1)
            ->selectRaw('UPPER(LEFT(kata, 1)) as letter, COUNT(*) as count')
            ->groupBy('letter')
            ->pluck('count', 'letter')
            ->toArray();

        return Inertia::render('Frontend/Kamus', [
            'kamus'        => $kamus,
            'search'       => $request->search,
            'letter'       => $request->letter,
            'letterCounts' => $letterCounts,
            'locale'       => app()->getLocale(),
        ]);
    }
}
