<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\KamusResource;
use App\Models\Kamus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KamusController extends Controller
{
    private const PER_PAGE_DEFAULT = 15;
    private const PER_PAGE_MAX     = 100;

    /**
     * GET /api/v1/kamus
     *
     * Query params:
     *   search    - cari di bahasa_melayu, bahasa_indonesia, keterangan
     *   huruf     - filter berdasarkan huruf awal (A-Z)
     *   per_page  - item per halaman, maks 100  (default: 15)
     *   page      - nomor halaman
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search'   => ['sometimes', 'string', 'max:100'],
            'huruf'    => ['sometimes', 'string', 'size:1', 'regex:/^[A-Za-z]$/'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:' . self::PER_PAGE_MAX],
        ]);

        $query = Kamus::where('status', 1);

        if (! empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->where('bahasa_melayu', 'LIKE', '%' . $search . '%')
                  ->orWhere('bahasa_indonesia', 'LIKE', '%' . $search . '%')
                  ->orWhere('keterangan', 'LIKE', '%' . $search . '%');
            });
        }

        if (! empty($validated['huruf'])) {
            $query->whereRaw('LOWER(LEFT(bahasa_melayu, 1)) = ?', [strtolower($validated['huruf'])]);
        }

        $query->orderBy('bahasa_melayu', 'asc');

        $perPage  = $validated['per_page'] ?? self::PER_PAGE_DEFAULT;
        $paginate = $query->paginate($perPage)->appends($request->query());

        // Jumlah kata per huruf untuk kebutuhan navigasi A-Z
        $letterCounts = Kamus::where('status', 1)
            ->selectRaw('UPPER(LEFT(bahasa_melayu, 1)) as letter, COUNT(*) as count')
            ->groupBy('letter')
            ->orderBy('letter')
            ->pluck('count', 'letter');

        return response()->json([
            'success' => true,
            'message' => 'Data kamus berhasil diambil',
            'data'    => KamusResource::collection($paginate),
            'meta'    => [
                'current_page' => $paginate->currentPage(),
                'last_page'    => $paginate->lastPage(),
                'per_page'     => $paginate->perPage(),
                'total'        => $paginate->total(),
                'from'         => $paginate->firstItem(),
                'to'           => $paginate->lastItem(),
                'letter_counts' => $letterCounts,
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
     * GET /api/v1/kamus/{id}
     *
     * Menampilkan detail satu entri kamus berdasarkan ID.
     */
    public function show(int $id): JsonResponse
    {
        $kamus = Kamus::where('id', $id)
            ->where('status', 1)
            ->first();

        if (! $kamus) {
            return response()->json([
                'success' => false,
                'message' => 'Entri kamus tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data kamus berhasil diambil',
            'data'    => new KamusResource($kamus),
        ]);
    }
}
