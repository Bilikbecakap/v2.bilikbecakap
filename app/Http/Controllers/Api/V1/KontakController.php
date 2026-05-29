<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Kontak;
use Illuminate\Http\Request;

class KontakController extends Controller
{
    public function index(Request $request)
    {
        $query = Kontak::latest();

        if ($request->filled('sumber')) {
            $query->where('sumber', $request->sumber);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('subjek', 'like', '%' . $request->search . '%');
            });
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $kontaks = $query->paginate($perPage);

        return response()->json($kontaks);
    }

    public function show(Kontak $kontak)
    {
        return response()->json($kontak);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'          => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'nomor_telepon' => 'nullable|string|max:20',
            'subjek'        => 'required|string|max:255',
            'pesan'         => 'required|string|min:10',
            'sumber'        => 'required|string|max:255',
        ]);

        Kontak::create($validated);

        return response()->json(['message' => 'Pesan berhasil dikirim.'], 201);
    }
}
