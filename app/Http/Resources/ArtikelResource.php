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
            'judul'            => $this->judul_indonesia,
            'excerpt'          => $this->excerpt_indonesia,
            'konten'           => $this->when($hasKonten, $this->konten_indonesia),
            'url'              => route('artikel.show', $this->slug),
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
