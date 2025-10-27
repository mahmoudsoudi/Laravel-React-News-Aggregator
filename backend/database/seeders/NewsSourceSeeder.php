<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NewsSource;

class NewsSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sources = [
            [
                'name' => 'NewsAPI.org',
                'slug' => 'newsapi',
                'description' => 'Comprehensive API with access to articles from more than 70,000 news sources',
                'url' => 'https://newsapi.org',
                'api_url' => 'https://newsapi.org',
                'api_key' => env('NEWSAPI_KEY'),
                'api_config' => [
                    'endpoints' => [
                        'everything' => '/v2/everything',
                        'top_headlines' => '/v2/top-headlines'
                    ],
                    'rate_limit' => 1000,
                    'rate_limit_period' => 'day'
                ],
                'logo_url' => 'https://newsapi.org/images/n-logo_big.png',
                'country' => 'US',
                'language' => 'en',
                'is_active' => true,
                'fetch_interval_minutes' => 60
            ],
            [
                'name' => 'The Guardian',
                'slug' => 'guardian',
                'description' => 'Access to articles from The Guardian newspaper',
                'url' => 'https://www.theguardian.com',
                'api_url' => 'https://content.guardianapis.com',
                'api_key' => env('GUARDIAN_API_KEY'),
                'api_config' => [
                    'endpoints' => [
                        'search' => '/search',
                        'sections' => '/sections'
                    ],
                    'rate_limit' => 5000,
                    'rate_limit_period' => 'day'
                ],
                'logo_url' => 'https://assets.guim.co.uk/images/guardian-logo-rss.png',
                'country' => 'UK',
                'language' => 'en',
                'is_active' => true,
                'fetch_interval_minutes' => 60
            ],
            [
                'name' => 'New York Times',
                'slug' => 'nytimes',
                'description' => 'Access to articles from The New York Times',
                'url' => 'https://www.nytimes.com',
                'api_url' => 'https://api.nytimes.com',
                'api_key' => env('NYTIMES_API_KEY'),
                'api_config' => [
                    'endpoints' => [
                        'article_search' => '/svc/search/v2/articlesearch.json',
                        'top_stories' => '/svc/topstories/v2'
                    ],
                    'rate_limit' => 1000,
                    'rate_limit_period' => 'day'
                ],
                'logo_url' => 'https://static01.nyt.com/images/misc/nytlogo152x23.png',
                'country' => 'US',
                'language' => 'en',
                'is_active' => true,
                'fetch_interval_minutes' => 60
            ],
            [
                'name' => 'BBC News',
                'slug' => 'bbc',
                'description' => 'Access to news from BBC News',
                'url' => 'https://www.bbc.com/news',
                'api_url' => 'https://newsapi.org/v2', // Using NewsAPI for BBC
                'api_key' => env('NEWSAPI_KEY'),
                'api_config' => [
                    'endpoints' => [
                        'everything' => '/everything',
                        'top_headlines' => '/top-headlines'
                    ],
                    'sources' => 'bbc-news',
                    'rate_limit' => 1000,
                    'rate_limit_period' => 'day'
                ],
                'logo_url' => 'https://static.bbci.co.uk/news/1.2.0/img/bbc_news_120x60.png',
                'country' => 'UK',
                'language' => 'en',
                'is_active' => true,
                'fetch_interval_minutes' => 60
            ],
            [
                'name' => 'OpenNews',
                'slug' => 'opennews',
                'description' => 'Open source news aggregation',
                'url' => 'https://opennews.org',
                'api_url' => 'https://newsapi.org/v2', // Using NewsAPI as proxy
                'api_key' => env('NEWSAPI_KEY'),
                'api_config' => [
                    'endpoints' => [
                        'everything' => '/everything',
                        'top_headlines' => '/top-headlines'
                    ],
                    'rate_limit' => 1000,
                    'rate_limit_period' => 'day'
                ],
                'logo_url' => null,
                'country' => 'US',
                'language' => 'en',
                'is_active' => true,
                'fetch_interval_minutes' => 90
            ],
            [
                'name' => 'NewsCred',
                'slug' => 'newscred',
                'description' => 'NewsCred API for news content',
                'url' => 'https://www.newscred.com',
                'api_url' => 'https://newsapi.org/v2', // Using NewsAPI as proxy
                'api_key' => env('NEWSAPI_KEY'),
                'api_config' => [
                    'endpoints' => [
                        'everything' => '/everything',
                        'top_headlines' => '/top-headlines'
                    ],
                    'rate_limit' => 1000,
                    'rate_limit_period' => 'day'
                ],
                'logo_url' => null,
                'country' => 'US',
                'language' => 'en',
                'is_active' => true,
                'fetch_interval_minutes' => 90
            ]
        ];

        foreach ($sources as $sourceData) {
            NewsSource::updateOrCreate(
                ['slug' => $sourceData['slug']],
                $sourceData
            );
        }
    }
}
