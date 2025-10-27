<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Get the news for the category.
     */
    public function news(): HasMany
    {
        return $this->hasMany(News::class);
    }

    /**
     * Scope a query to only include active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get the default color if none is set.
     */
    public function getColorAttribute($value)
    {
        return $value ?: '#6B7280'; // Default gray color
    }

    /**
     * Get the default icon if none is set.
     */
    public function getIconAttribute($value)
    {
        return $value ?: 'fas fa-newspaper'; // Default newspaper icon
    }

    /**
     * Get the count of news in this category.
     */
    public function getNewsCountAttribute()
    {
        return $this->news()->active()->count();
    }

    /**
     * Get the latest news in this category.
     */
    public function getLatestNews($limit = 5)
    {
        return $this->news()
                   ->active()
                   ->orderBy('published_at', 'desc')
                   ->limit($limit)
                   ->get();
    }
}
