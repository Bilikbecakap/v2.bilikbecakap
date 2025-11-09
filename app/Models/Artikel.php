<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Artikel extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'artikel';
    
    protected $fillable = [
        'judul_indonesia',
        'judul_melayu',
        'judul_english',
        'konten_indonesia',
        'konten_melayu',
        'konten_english',
        'slug',
        'kategori_id',
        'gambar_thumbnail',
        'meta_keywords',
        'status',
        'is_featured',
        'tanggal_publish',
        'views_count',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'tanggal_publish' => 'datetime',
        'views_count' => 'integer',
    ];

    // Relationships
    public function kategori()
    {
        return $this->belongsTo(MasterArtikel::class, 'kategori_id');
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
    public function getExcerptIndonesiaAttribute()
    {
        return $this->generateExcerpt($this->konten_indonesia);
    }

    public function getExcerptMelayuAttribute()
    {
        return $this->generateExcerpt($this->konten_melayu);
    }

    public function getExcerptEnglishAttribute()
    {
        return $this->generateExcerpt($this->konten_english);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
            'pending' => 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
            'published' => 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
            'archived' => 'bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200',
        ];

        return $badges[$this->status] ?? $badges['draft'];
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function getMetaKeywordsArrayAttribute()
    {
        return $this->meta_keywords ? array_map('trim', explode(',', $this->meta_keywords)) : [];
    }

    public function setMetaKeywordsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['meta_keywords'] = implode(',', array_filter($value));
        } else {
            $this->attributes['meta_keywords'] = $value;
        }
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
        $title = $title ?: $this->judul_indonesia ?: $this->judul_melayu ?: $this->judul_english;
        
        if (!$title) {
            $title = 'artikel-' . time();
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
        $this->increment('views_count');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('tanggal_publish', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByKategori($query, $kategoriId)
    {
        return $query->where('kategori_id', $kategoriId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('judul_indonesia', 'LIKE', '%' . $search . '%')
              ->orWhere('judul_melayu', 'LIKE', '%' . $search . '%')
              ->orWhere('judul_english', 'LIKE', '%' . $search . '%')
              ->orWhere('meta_keywords', 'LIKE', '%' . $search . '%');
        });
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'judul_indonesia', 'judul_melayu', 'judul_english',
                'slug', 'kategori_id', 'status', 'is_featured', 'tanggal_publish'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Artikel has been {$eventName}");
    }

    // Boot method untuk auto generate slug
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($artikel) {
            if (!$artikel->slug) {
                $artikel->slug = $artikel->generateSlug();
            }
        });

        static::updating(function ($artikel) {
            if ($artikel->isDirty(['judul_indonesia', 'judul_melayu', 'judul_english']) && !$artikel->isDirty('slug')) {
                $artikel->slug = $artikel->generateSlug();
            }
        });
    }

    public function komentars()
    {
        return $this->morphMany(Komentar::class, 'commentable')->where('status', 'approved');
    }

}