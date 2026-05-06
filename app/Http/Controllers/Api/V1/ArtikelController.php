<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArtikelResource;
use App\Models\Artikel;
use App\Models\MasterArtikel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ArtikelController extends Controller
{
    private const PER_PAGE_DEFAULT = 10;
    private const PER_PAGE_MAX     = 50;

    /**
     * GET /api/v1/artikel
     *
     * Query params:
     *   search       - cari berdasarkan judul atau keyword
     *   kategori_id  - filter berdasarkan ID kategori
     *   featured     - filter artikel unggulan (true/false)
     *   sort         - urutan kolom: judul | views | tanggal  (default: tanggal)
     *   direction    - asc | desc  (default: desc)
     *   per_page     - jumlah item per halaman, maks 50  (default: 10)
     *   page         - nomor halaman
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search'      => ['sometimes', 'string', 'max:100'],
            'kategori_id' => ['sometimes', 'integer', 'min:1'],
            'featured'    => ['sometimes', 'boolean'],
            'sort'        => ['sometimes', Rule::in(['judul', 'views', 'tanggal'])],
            'direction'   => ['sometimes', Rule::in(['asc', 'desc'])],
            'per_page'    => ['sometimes', 'integer', 'min:1', 'max:' . self::PER_PAGE_MAX],
        ]);

        $query = Artikel::with('kategori')
            ->select([
                'id', 'slug',
                'judul_indonesia', 'judul_melayu', 'judul_english',
                'konten_indonesia', 'konten_melayu', 'konten_english',
                'gambar_thumbnail', 'kategori_id',
                'meta_keywords', 'views_count', 'is_featured', 'tanggal_publish', 'created_at',
            ])
            ->published();

        if (! empty($validated['search'])) {
            $query->search($validated['search']);
        }

        if (! empty($validated['kategori_id'])) {
            $query->byKategori($validated['kategori_id']);
        }

        if (isset($validated['featured'])) {
            $query->where('is_featured', $validated['featured']);
        }

        $direction = $validated['direction'] ?? 'desc';

        match ($validated['sort'] ?? 'tanggal') {
            'judul'   => $query->orderBy('judul_indonesia', $direction),
            'views'   => $query->orderBy('views_count', $direction),
            default   => $query->orderBy('tanggal_publish', $direction),
        };

        $perPage  = $validated['per_page'] ?? self::PER_PAGE_DEFAULT;
        $paginate = $query->paginate($perPage)->appends($request->query());

        return response()->json([
            'success' => true,
            'message' => 'Data artikel berhasil diambil',
            'data'    => ArtikelResource::collection($paginate),
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
     * GET /api/v1/artikel/{slug}
     *
     * Menampilkan detail artikel berdasarkan slug.
     * View count akan diinkremen setiap kali endpoint ini dipanggil.
     */
    public function show(string $slug): JsonResponse
    {
        $artikel = Artikel::with('kategori')
            ->where('slug', $slug)
            ->published()
            ->first();

        if (! $artikel) {
            return response()->json([
                'success' => false,
                'message' => 'Artikel tidak ditemukan',
            ], 404);
        }

        $artikel->incrementViews();

        return response()->json([
            'success' => true,
            'message' => 'Data artikel berhasil diambil',
            'data'    => new ArtikelResource($artikel),
        ]);
    }

    /**
     * GET /api/v1/artikel/kategori
     *
     * Mengembalikan daftar kategori artikel yang aktif.
     */
    public function kategori(): JsonResponse
    {
        $kategori = MasterArtikel::where('is_active', true)
            ->orderBy('urutan')
            ->orderBy('nama_kategori')
            ->get(['id', 'nama_kategori', 'deskripsi']);

        return response()->json([
            'success' => true,
            'message' => 'Data kategori berhasil diambil',
            'data'    => $kategori,
        ]);
    }
}
