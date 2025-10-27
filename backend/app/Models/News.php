<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'content',
        'url',
        'image_url',
        'author',
        'published_at',
        'news_source_id',
        'category_id',
        'external_id',
        'metadata',
        'is_active'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'metadata' => 'array',
        'is_active' => 'boolean'
    ];

    protected $dates = [
        'published_at'
    ];

    /**
     * Get the news source that owns the news.
     */
    public function newsSource(): BelongsTo
    {
        return $this->belongsTo(NewsSource::class);
    }

    /**
     * Get the category that owns the news.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope a query to only include active news.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include recent news.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('published_at', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope a query to filter by news source.
     */
    public function scopeBySource($query, $sourceId)
    {
        return $query->where('news_source_id', $sourceId);
    }

    /**
     * Scope a query to search news by title and description.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Get the formatted published date.
     */
    public function getFormattedPublishedAtAttribute()
    {
        return $this->published_at->format('M d, Y H:i');
    }

    /**
     * Get the time ago string.
     */
    public function getTimeAgoAttribute()
    {
        return $this->published_at->diffForHumans();
    }

    /**
     * Get the excerpt of the description.
     */
    public function getExcerptAttribute($length = 150)
    {
        return \Str::limit($this->description, $length);
    }
}
