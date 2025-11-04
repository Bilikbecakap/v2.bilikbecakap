<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class MasterMediaMusicQuiz extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'master_media_music_quiz';
    
    protected $fillable = [
        'audio',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['audio_url'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['audio', 'keterangan', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Master Media Music Quiz has been {$eventName}");
    }

    // Accessor untuk mendapatkan URL audio
    public function getAudioUrlAttribute()
    {
        if (!$this->audio) {
            return null;
        }

        // Pastikan file exists
        if (Storage::disk('public')->exists($this->audio)) {
            return asset('storage/' . $this->audio);
        }

        return null;
    }
}