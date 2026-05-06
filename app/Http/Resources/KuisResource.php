<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KuisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'slug'           => $this->slug,
            'title'          => $this->title,
            'description'    => $this->description,
            'thumbnail_url'  => $this->thumbnail_url,
            'music_url'      => $this->music_url,
            'duration'       => $this->duration,
            'type'           => $this->type,
            'is_duel_enabled' => $this->is_duel_enabled,
            'total_questions' => $this->total_questions,
            // Statistik — hanya tersedia saat detail (di-set via additional)
            'stats'          => $this->when(
                isset($this->resource->stats),
                $this->resource->stats ?? null
            ),
            'modul'          => $this->whenLoaded('modulPembelajaran', fn () => [
                'id'    => $this->modulPembelajaran->id,
                'title' => $this->modulPembelajaran->title,
                'slug'  => $this->modulPembelajaran->slug,
            ]),
        ];
    }
}
