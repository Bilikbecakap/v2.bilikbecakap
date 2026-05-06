<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PembelajaranResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'slug'           => $this->slug,
            'title'          => $this->title,
            'deskripsi'      => $this->deskripsi,
            'excerpt'        => $this->excerpt,
            // Field detail — hanya muncul saat content/pdf/video dimuat (endpoint show)
            'content'        => $this->when(! is_null($this->content), $this->content),
            'pdf_url'        => $this->when(! is_null($this->pdf_file), $this->pdf_url),
            'video_embed_id' => $this->when(! is_null($this->video_embed), $this->video_embed_id),
            'thumbnail_url'  => $this->thumbnail_url,
            'kategori'       => $this->whenLoaded('category', fn () => [
                'id'   => $this->category->id,
                'nama' => $this->category->nama_kategori,
            ]),
            'view_count'     => $this->view_count,
            'reading_time'   => $this->reading_time,
            'tanggal_publish' => $this->tanggal_publish?->toISOString(),
            'created_at'     => $this->created_at?->toISOString(),
        ];
    }
}
