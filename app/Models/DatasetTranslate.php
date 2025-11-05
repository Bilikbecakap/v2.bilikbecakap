<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class DatasetTranslate extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'dataset_translate'; 
    
    protected $fillable = [
        'bahasa_belitung',
        'bahasa_indonesia',
    ];

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
            ->logOnly(['bahasa_belitung', 'bahasa_indonesia'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Dataset has been {$eventName}");
    }
}
