<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class GambarGaleri extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'gambar_galeri';
    
    protected $fillable = [
        'gambar',
        'master_gambar_id',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['gambar_url'];

    // Relasi ke Master Gambar
    public function masterGambar()
    {
        return $this->belongsTo(MasterGambar::class, 'master_gambar_id');
    }

    // Accessor untuk mendapatkan URL gambar
    public function getGambarUrlAttribute()
    {
        if (!$this->gambar) {
            return null;
        }

        if (Storage::disk('public')->exists($this->gambar)) {
            return asset('storage/' . $this->gambar);
        }

        return null;
    }

    // Method untuk menghapus gambar
    public function deleteGambar()
    {
        if ($this->gambar && Storage::disk('public')->exists($this->gambar)) {
            Storage::disk('public')->delete($this->gambar);
        }
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['gambar', 'master_gambar_id', 'keterangan', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Gambar Galeri has been {$eventName}");
    }
}