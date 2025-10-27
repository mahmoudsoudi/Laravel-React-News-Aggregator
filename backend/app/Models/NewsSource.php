<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class NewsSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'url',
        'api_url',
        'api_key',
        'api_config',
        'logo_url',
        'country',
        'language',
        'is_active',
        'last_fetched_at',
        'fetch_interval_minutes'
    ];

    protected $casts = [
        'api_config' => 'array',
        'is_active' => 'boolean',
        'last_fetched_at' => 'datetime',
        'fetch_interval_minutes' => 'integer'
    ];

    protected $dates = [
        'last_fetched_at'
    ];

    /**
     * Get the news for the news source.
     */
    public function news(): HasMany
    {
        return $this->hasMany(News::class);
    }

    /**
     * Scope a query to only include active sources.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include sources ready for fetching.
     */
    public function scopeReadyForFetch($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('last_fetched_at')
                          ->orWhereRaw('last_fetched_at <= NOW() - INTERVAL \'1 minute\' * fetch_interval_minutes');
                    });
    }

    /**
     * Scope a query to filter by language.
     */
    public function scopeByLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    /**
     * Scope a query to filter by country.
     */
    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Check if the source is ready for fetching.
     */
    public function isReadyForFetch(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if (!$this->last_fetched_at) {
            return true;
        }

        return $this->last_fetched_at->addMinutes($this->fetch_interval_minutes)->isPast();
    }

    /**
     * Update the last fetched timestamp.
     */
    public function markAsFetched(): void
    {
        $this->update(['last_fetched_at' => Carbon::now()]);
    }

    /**
     * Get the API configuration for this source.
     */
    public function getApiConfig($key = null, $default = null)
    {
        if ($key === null) {
            return $this->api_config ?? [];
        }

        return data_get($this->api_config, $key, $default);
    }

    /**
     * Set the API configuration for this source.
     */
    public function setApiConfig($key, $value): void
    {
        $config = $this->api_config ?? [];
        data_set($config, $key, $value);
        $this->update(['api_config' => $config]);
    }
}
