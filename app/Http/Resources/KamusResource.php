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
            'id'               => $this->id,
            'kata'             => $this->kata,
            'pos'              => $this->pos,
            'definisi'         => $this->definisi,
            'catatan_validasi' => $this->catatan_validasi,
            'audio_url'        => $this->audio
                ? Storage::disk('public')->url($this->audio)
                : null,
            'contoh'           => $this->whenLoaded('contoh', function () {
                return $this->contoh->map(fn ($c) => [
                    'id'      => $c->id,
                    'kalimat' => $c->contoh_kalimat,
                    'arti'    => $c->arti_contoh_kalimat,
                ]);
            }, []),
            'created_at'       => $this->created_at?->toISOString(),
        ];
    }
}
