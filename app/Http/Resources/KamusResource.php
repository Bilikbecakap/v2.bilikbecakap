<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class KamusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'bahasa_melayu'   => $this->bahasa_melayu,
            'bahasa_indonesia' => $this->bahasa_indonesia,
            'keterangan'      => $this->keterangan,
            'audio_url'       => $this->audio
                ? Storage::disk('public')->url($this->audio)
                : null,
            'created_at'      => $this->created_at?->toISOString(),
        ];
    }
}
