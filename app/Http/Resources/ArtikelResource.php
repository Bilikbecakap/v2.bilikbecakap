<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ArtikelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $hasKonten = ! is_null($this->konten_indonesia)
            || ! is_null($this->konten_melayu)
            || ! is_null($this->konten_english);

        return [
            'id'               => $this->id,
            'slug'             => $this->slug,
            'judul'            => [
                'id' => $this->judul_indonesia,
                'ms' => $this->judul_melayu,
                'en' => $this->judul_english,
            ],
            'excerpt'          => [
                'id' => $this->excerpt_indonesia,
                'ms' => $this->excerpt_melayu,
                'en' => $this->excerpt_english,
            ],
            'konten'           => $this->when($hasKonten, [
                'id' => $this->konten_indonesia,
                'ms' => $this->konten_melayu,
                'en' => $this->konten_english,
            ]),
            'gambar_thumbnail' => $this->gambar_thumbnail
                ? Storage::disk('public')->url($this->gambar_thumbnail)
                : null,
            'kategori'         => $this->whenLoaded('kategori', fn () => [
                'id'   => $this->kategori->id,
                'nama' => $this->kategori->nama_kategori,
            ]),
            'meta_keywords'    => $this->meta_keywords_array,
            'views_count'      => $this->views_count,
            'is_featured'      => $this->is_featured,
            'tanggal_publish'  => $this->tanggal_publish?->toISOString(),
            'created_at'       => $this->created_at?->toISOString(),
        ];
    }
}
