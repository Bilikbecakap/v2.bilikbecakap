<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ModulPembelajaran extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'modul_pembelajaran';
    
    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'deskripsi',
        'content',
        'thumbnail',
        'pdf_file',
        'video_embed',
        'status',
        'tanggal_publish',
        'view_count',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_publish' => 'datetime',
        'view_count' => 'integer',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(MasterModul::class, 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Accessors & Mutators
    public function getExcerptAttribute()
    {
        return $this->generateExcerpt($this->deskripsi ?: $this->content);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
            'published' => 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
            'archived' => 'bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200',
        ];

        return $badges[$this->status] ?? $badges['draft'];
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail) {
            return asset('storage/' . $this->thumbnail);
        }
        return null;
    }

    public function getPdfUrlAttribute()
    {
        if ($this->pdf_file) {
            return asset('storage/' . $this->pdf_file);
        }
        return null;
    }

    public function getVideoEmbedIdAttribute()
    {
        if ($this->video_embed) {
            // Extract YouTube video ID from various URL formats
            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $this->video_embed, $matches);
            return isset($matches[1]) ? $matches[1] : null;
        }
        return null;
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('tanggal_publish', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('title', 'LIKE', '%' . $search . '%')
              ->orWhere('deskripsi', 'LIKE', '%' . $search . '%')
              ->orWhere('content', 'LIKE', '%' . $search . '%');
        });
    }

    public function scopePopular($query, $limit = 10)
    {
        return $query->orderBy('view_count', 'desc')->limit($limit);
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->published()
                    ->orderBy('tanggal_publish', 'desc')
                    ->limit($limit);
    }

    // Helper Methods
    private function generateExcerpt($content, $wordLimit = 20)
    {
        if (!$content) return null;
        
        $text = strip_tags($content);
        $words = explode(' ', $text);
        
        if (count($words) <= $wordLimit) {
            return $text;
        }
        
        return implode(' ', array_slice($words, 0, $wordLimit)) . '...';
    }

    public function generateSlug($title = null)
    {
        $title = $title ?: $this->title;
        
        if (!$title) {
            $title = 'modul-' . time();
        }

        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function incrementViews()
    {
        $this->increment('view_count');
    }

    public function isPublished()
    {
        return $this->status === 'published' && 
               $this->tanggal_publish && 
               $this->tanggal_publish <= now();
    }

    public function canBeViewed()
    {
        return $this->isPublished();
    }

    public function getReadingTimeAttribute()
    {
        if (!$this->content) return 0;
        
        $wordCount = str_word_count(strip_tags($this->content));
        $readingSpeed = 200; // words per minute
        
        return max(1, ceil($wordCount / $readingSpeed));
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'title', 'slug', 'category_id', 'status', 
                'tanggal_publish', 'thumbnail', 'pdf_file', 'video_embed'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Modul Pembelajaran has been {$eventName}");
    }

    // Boot method untuk auto generate slug
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($modul) {
            if (!$modul->slug) {
                $modul->slug = $modul->generateSlug();
            }
            
            // Set tanggal_publish jika status published dan belum ada tanggal
            if ($modul->status === 'published' && !$modul->tanggal_publish) {
                $modul->tanggal_publish = now();
            }
        });

        static::updating(function ($modul) {
            // Auto generate slug jika title berubah tapi slug tidak diubah manual
            if ($modul->isDirty('title') && !$modul->isDirty('slug')) {
                $modul->slug = $modul->generateSlug();
            }

            // Set tanggal_publish ketika status berubah ke published
            if ($modul->isDirty('status') && 
                $modul->status === 'published' && 
                !$modul->tanggal_publish) {
                $modul->tanggal_publish = now();
            }
        });
    }

    public function komentars()
    {
        return $this->morphMany(Komentar::class, 'commentable')->where('status', 'approved');
    }
}