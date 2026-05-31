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
        'kata',
        'pos',
        'definisi',
        'audio',
        'catatan_validasi',
        'status',
        'created_by',
        'updated_by',
    ];

    public function getStatusTextAttribute()
    {
        $statusTexts = [
            1 => 'aktif',
            2 => 'tidak aktif',
            3 => 'menunggu',
            4 => 'menunggu finalisasi',
        ];

        return $statusTexts[$this->status] ?? 'unknown';
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function contoh()
    {
        return $this->hasMany(KamusContoh::class, 'kamus_id');
    }

    public function validasiRecords()
    {
        return $this->hasMany(KamusValidasi::class, 'kamus_id');
    }

    public function validasiDisetujui()
    {
        return $this->hasMany(KamusValidasi::class, 'kamus_id')->where('action', 'setuju');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['kata', 'definisi', 'status', 'catatan_validasi'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Kamus has been {$eventName}");
    }
}
