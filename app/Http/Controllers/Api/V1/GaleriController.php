<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\GaleriResource;
use App\Models\GambarGaleri;
use App\Models\MasterGambar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GaleriController extends Controller
{
    private const PER_PAGE_DEFAULT = 12;
    private const PER_PAGE_MAX     = 100;

    /**
     * GET /api/v1/galeri
     *
     * Query params:
     *   kategori_id - filter berdasarkan ID kategori gambar
     *   per_page    - item per halaman, maks 100  (default: 12)
     *   page        - nomor halaman
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kategori_id' => ['sometimes', 'integer', 'min:1'],
            'per_page'    => ['sometimes', 'integer', 'min:1', 'max:' . self::PER_PAGE_MAX],
        ]);

        $query = GambarGaleri::with('masterGambar')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc');

        if (! empty($validated['kategori_id'])) {
            $query->where('master_gambar_id', $validated['kategori_id']);
        }

        $perPage  = $validated['per_page'] ?? self::PER_PAGE_DEFAULT;
        $paginate = $query->paginate($perPage)->appends($request->query());

        return response()->json([
            'success' => true,
            'message' => 'Data galeri berhasil diambil',
            'data'    => GaleriResource::collection($paginate),
            'meta'    => [
                'current_page' => $paginate->currentPage(),
                'last_page'    => $paginate->lastPage(),
                'per_page'     => $paginate->perPage(),
                'total'        => $paginate->total(),
                'from'         => $paginate->firstItem(),
                'to'           => $paginate->lastItem(),
            ],
            'links'   => [
                'first' => $paginate->url(1),
                'last'  => $paginate->url($paginate->lastPage()),
                'prev'  => $paginate->previousPageUrl(),
                'next'  => $paginate->nextPageUrl(),
            ],
        ]);
    }

    /**
     * GET /api/v1/galeri/{id}
     *
     * Menampilkan detail satu gambar galeri berdasarkan ID.
     */
    public function show(int $id): JsonResponse
    {
        $galeri = GambarGaleri::with('masterGambar')
            ->where('id', $id)
            ->where('is_active', true)
            ->first();

        if (! $galeri) {
            return response()->json([
                'success' => false,
                'message' => 'Gambar tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data galeri berhasil diambil',
            'data'    => new GaleriResource($galeri),
        ]);
    }

    /**
     * GET /api/v1/galeri/kategori
     *
     * Mengembalikan daftar kategori galeri yang aktif beserta jumlah gambar.
     */
    public function kategori(): JsonResponse
    {
        $kategori = MasterGambar::where('is_active', true)
            ->withCount(['galeriImages' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('urutan')
            ->orderBy('nama_kategori')
            ->get(['id', 'nama_kategori', 'deskripsi', 'urutan']);

        return response()->json([
            'success' => true,
            'message' => 'Data kategori galeri berhasil diambil',
            'data'    => $kategori->map(fn ($k) => [
                'id'           => $k->id,
                'nama'         => $k->nama_kategori,
                'deskripsi'    => $k->deskripsi,
                'jumlah_gambar' => $k->galeri_images_count,
            ]),
        ]);
    }
}
