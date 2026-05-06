<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PembelajaranResource;
use App\Models\MasterModul;
use App\Models\ModulPembelajaran;
use App\Models\Quizzes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PembelajaranController extends Controller
{
    private const PER_PAGE_DEFAULT = 12;
    private const PER_PAGE_MAX     = 50;

    /**
     * GET /api/v1/pembelajaran
     *
     * Query params:
     *   search      - cari berdasarkan title, deskripsi
     *   category_id - filter berdasarkan ID kategori
     *   sort        - urutan: title | views | tanggal_publish | created_at  (default: tanggal_publish)
     *   direction   - asc | desc  (default: desc)
     *   per_page    - item per halaman, maks 50  (default: 12)
     *   page        - nomor halaman
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search'      => ['sometimes', 'string', 'max:100'],
            'category_id' => ['sometimes', 'integer', 'min:1'],
            'sort'        => ['sometimes', Rule::in(['title', 'views', 'tanggal_publish', 'created_at'])],
            'direction'   => ['sometimes', Rule::in(['asc', 'desc'])],
            'per_page'    => ['sometimes', 'integer', 'min:1', 'max:' . self::PER_PAGE_MAX],
        ]);

        $query = ModulPembelajaran::with('category')
            ->select([
                'id', 'slug', 'title', 'deskripsi', 'thumbnail',
                'view_count', 'category_id', 'tanggal_publish', 'created_at',
            ])
            ->published();

        if (! empty($validated['search'])) {
            $query->search($validated['search']);
        }

        if (! empty($validated['category_id'])) {
            $query->byCategory($validated['category_id']);
        }

        $direction = $validated['direction'] ?? 'desc';

        match ($validated['sort'] ?? 'tanggal_publish') {
            'title'          => $query->orderBy('title', $direction),
            'views'          => $query->orderBy('view_count', $direction),
            'created_at'     => $query->orderBy('created_at', $direction),
            default          => $query->orderBy('tanggal_publish', $direction),
        };

        $perPage  = $validated['per_page'] ?? self::PER_PAGE_DEFAULT;
        $paginate = $query->paginate($perPage)->appends($request->query());

        return response()->json([
            'success' => true,
            'message' => 'Data pembelajaran berhasil diambil',
            'data'    => PembelajaranResource::collection($paginate),
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
     * GET /api/v1/pembelajaran/{slug}
     *
     * Menampilkan detail modul beserta kuis terkait (jika ada).
     * View count akan diinkremen setiap kali endpoint ini dipanggil.
     */
    public function show(string $slug): JsonResponse
    {
        $modul = ModulPembelajaran::with('category')
            ->select([
                'id', 'slug', 'title', 'deskripsi', 'content', 'thumbnail',
                'pdf_file', 'video_embed', 'view_count', 'category_id',
                'tanggal_publish', 'created_at',
            ])
            ->where('slug', $slug)
            ->published()
            ->first();

        if (! $modul) {
            return response()->json([
                'success' => false,
                'message' => 'Modul pembelajaran tidak ditemukan',
            ], 404);
        }

        $modul->incrementViews();

        $relatedQuiz = Quizzes::where('modul_pembelajaran_id', $modul->id)
            ->where('type', 'modul')
            ->where('status', 'active')
            ->select(['id', 'title', 'slug', 'description', 'thumbnail', 'duration'])
            ->first();

        return response()->json([
            'success'      => true,
            'message'      => 'Data pembelajaran berhasil diambil',
            'data'         => new PembelajaranResource($modul),
            'related_quiz' => $relatedQuiz,
        ]);
    }

    /**
     * GET /api/v1/pembelajaran/kategori
     *
     * Mengembalikan daftar kategori modul yang aktif.
     */
    public function kategori(): JsonResponse
    {
        $kategori = MasterModul::where('is_active', true)
            ->orderBy('urutan')
            ->orderBy('nama_kategori')
            ->get(['id', 'nama_kategori', 'deskripsi']);

        return response()->json([
            'success' => true,
            'message' => 'Data kategori pembelajaran berhasil diambil',
            'data'    => $kategori,
        ]);
    }
}
