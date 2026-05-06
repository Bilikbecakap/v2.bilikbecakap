<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GaleriResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'gambar_url' => $this->gambar_url,
            'keterangan' => $this->keterangan,
            'kategori'   => $this->whenLoaded('masterGambar', fn () => [
                'id'   => $this->masterGambar->id,
                'nama' => $this->masterGambar->nama_kategori,
            ]),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
