<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KamusAudioGenerationBatch extends Model
{
    protected $fillable = [
        'status',
        'total_words',
        'processed',
        'success_count',
        'skipped_count',
        'failed_count',
        'voice',
        'error_message',
        'error_log',
        'started_by',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function isActive(): bool
    {
        return in_array($this->status, ['queued', 'running']);
    }

    public function starter()
    {
        return $this->belongsTo(User::class, 'started_by');
    }
}
