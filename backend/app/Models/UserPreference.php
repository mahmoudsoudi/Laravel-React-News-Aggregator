<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'preferred_sources',
        'preferred_categories',
        'excluded_sources',
        'excluded_categories',
        'language',
        'country',
        'items_per_page',
        'show_images',
        'auto_refresh',
        'refresh_interval_minutes',
        'notification_settings'
    ];

    protected $casts = [
        'preferred_sources' => 'array',
        'preferred_categories' => 'array',
        'excluded_sources' => 'array',
        'excluded_categories' => 'array',
        'items_per_page' => 'integer',
        'show_images' => 'boolean',
        'auto_refresh' => 'boolean',
        'refresh_interval_minutes' => 'integer',
        'notification_settings' => 'array'
    ];

    /**
     * Get the user that owns the preferences.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the preferred news sources.
     */
    public function getPreferredNewsSources()
    {
        if (!$this->preferred_sources) {
            return collect();
        }

        return NewsSource::whereIn('id', $this->preferred_sources)
                        ->active()
                        ->get();
    }

    /**
     * Get the preferred categories.
     */
    public function getPreferredCategories()
    {
        if (!$this->preferred_categories) {
            return collect();
        }

        return Category::whereIn('id', $this->preferred_categories)
                      ->active()
                      ->ordered()
                      ->get();
    }

    /**
     * Get the excluded news sources.
     */
    public function getExcludedNewsSources()
    {
        if (!$this->excluded_sources) {
            return collect();
        }

        return NewsSource::whereIn('id', $this->excluded_sources)->get();
    }

    /**
     * Get the excluded categories.
     */
    public function getExcludedCategories()
    {
        if (!$this->excluded_categories) {
            return collect();
        }

        return Category::whereIn('id', $this->excluded_categories)->get();
    }

    /**
     * Check if a news source is preferred.
     */
    public function isSourcePreferred($sourceId): bool
    {
        return in_array($sourceId, $this->preferred_sources ?? []);
    }

    /**
     * Check if a category is preferred.
     */
    public function isCategoryPreferred($categoryId): bool
    {
        return in_array($categoryId, $this->preferred_categories ?? []);
    }

    /**
     * Check if a news source is excluded.
     */
    public function isSourceExcluded($sourceId): bool
    {
        return in_array($sourceId, $this->excluded_sources ?? []);
    }

    /**
     * Check if a category is excluded.
     */
    public function isCategoryExcluded($categoryId): bool
    {
        return in_array($categoryId, $this->excluded_categories ?? []);
    }

    /**
     * Add a preferred source.
     */
    public function addPreferredSource($sourceId): void
    {
        $sources = $this->preferred_sources ?? [];
        if (!in_array($sourceId, $sources)) {
            $sources[] = $sourceId;
            $this->preferred_sources = $sources;
            $this->save();
        }
    }

    /**
     * Remove a preferred source.
     */
    public function removePreferredSource($sourceId): void
    {
        $sources = $this->preferred_sources ?? [];
        $sources = array_values(array_filter($sources, fn($id) => $id != $sourceId));
        $this->preferred_sources = $sources;
        $this->save();
    }

    /**
     * Add a preferred category.
     */
    public function addPreferredCategory($categoryId): void
    {
        $categories = $this->preferred_categories ?? [];
        if (!in_array($categoryId, $categories)) {
            $categories[] = $categoryId;
            $this->preferred_categories = $categories;
            $this->save();
        }
    }

    /**
     * Remove a preferred category.
     */
    public function removePreferredCategory($categoryId): void
    {
        $categories = $this->preferred_categories ?? [];
        $categories = array_values(array_filter($categories, fn($id) => $id != $categoryId));
        $this->preferred_categories = $categories;
        $this->save();
    }

    /**
     * Get notification setting.
     */
    public function getNotificationSetting($key, $default = null)
    {
        return data_get($this->notification_settings, $key, $default);
    }

    /**
     * Set notification setting.
     */
    public function setNotificationSetting($key, $value): void
    {
        $settings = $this->notification_settings ?? [];
        data_set($settings, $key, $value);
        $this->notification_settings = $settings;
        $this->save();
    }
}
