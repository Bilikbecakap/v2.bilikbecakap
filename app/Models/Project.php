<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'category',
        'technologies',
        'image',
        'gallery',
    ];

    protected $casts = [
        'technologies' => 'array',
        'gallery' => 'array',
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_DRAFT = 'draft';

    // Get available statuses
    public static function getStatuses()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_DRAFT => 'Draft',
        ];
    }

    // Get available categories
    public static function getCategories()
    {
        return [
            'Web Development',
            'Mobile Development',
            'Data Science',
            'UI/UX Design',
            'DevOps',
            'AI/ML',
        ];
    }

    // Get available technologies
    public static function getTechnologies()
    {
        return [
            'Laravel',
            'Vue.js',
            'React',
            'React Native',
            'MySQL',
            'PostgreSQL',
            'Python',
            'Django',
            'Chart.js',
            'Figma',
            'Docker',
            'AWS',
            'Node.js',
            'TypeScript',
            'Tailwind CSS',
        ];
    }

    // Accessor for formatted status
    public function getStatusLabelAttribute()
    {
        return self::getStatuses()[$this->status] ?? 'Unknown';
    }

    // Scope for filtering by status
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope for filtering by category
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Scope for search
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%")
                ->orWhere('category', 'LIKE', "%{$search}%");
        });
    }

    // Image helper methods
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    // Gallery helper methods
    public function getGalleryUrlsAttribute()
    {
        return $this->gallery ? collect($this->gallery)->map(fn($image) => asset('storage/' . $image))->toArray() : [];
    }

    public function getFirstImageAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return $this->gallery && !empty($this->gallery) ? asset('storage/' . $this->gallery[0]) : null;
    }
}
