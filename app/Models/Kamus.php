<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Kamus extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'kamus'; 
    
    protected $fillable = [
        'bahasa_melayu',
        'bahasa_indonesia',
        'audio',
        'keterangan',
        'status',
        'create_by',
        'update_by',
    ];

    public function getStatusTextAttribute()
    {
        $statusTexts = [
            1 => 'aktif',
            2 => 'tidak aktif',
            3 => 'menunggu',
        ];

        return $statusTexts[$this->status] ?? 'unknown';
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'create_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'update_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['bahasa_melayu', 'bahasa_indonesia', 'status', 'keterangan'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Kamus has been {$eventName}");
    }
}