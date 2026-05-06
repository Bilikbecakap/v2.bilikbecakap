<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Frontend\PenerjemahController as BasePenerjemahController;

/**
 * API Penerjemah — mewarisi seluruh logika terjemahan dari BasePenerjemahController.
 * Tidak ada duplikasi: semua metode translate/OCR sudah mengembalikan JsonResponse.
 */
class PenerjemahController extends BasePenerjemahController
{
    // Semua logika ada di parent:
    //   translate(Request)    → POST /api/v1/penerjemah
    //   extractText(Request)  → POST /api/v1/penerjemah/ocr
}
