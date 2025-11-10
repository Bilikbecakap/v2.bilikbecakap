<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\GambarGaleri;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GaleriController extends Controller
{
    public function index(Request $request)
    {
        $galeri = GambarGaleri::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->appends($request->query());

        return Inertia::render('Frontend/Gambar', [
            'galeri' => $galeri,
        ]);
    }
}