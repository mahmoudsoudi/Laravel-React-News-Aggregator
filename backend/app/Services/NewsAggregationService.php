<?php

namespace App\Services;

use App\Models\News;
use App\Models\NewsSource;
use App\Models\Category;
use App\Services\ApiResponseService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NewsAggregationService
{
    protected $apiResponseService;

    public function __construct(ApiResponseService $apiResponseService)
    {
        $this->apiResponseService = $apiResponseService;
    }

    /**
     * Fetch news from all active sources
     */
    public function fetchAllNews(): array
    {
        $sources = NewsSource::active()->readyForFetch()->get();
        $results = [];

        foreach ($sources as $source) {
            try {
                $result = $this->fetchFromSource($source);
                $results[] = [
                    'source' => $source->name,
                    'success' => $result['success'],
                    'count' => $result['count'],
                    'message' => $result['message']
                ];
            } catch (\Exception $e) {
                Log::error("Failed to fetch from source {$source->name}: " . $e->getMessage());
                $results[] = [
                    'source' => $source->name,
                    'success' => false,
                    'count' => 0,
                    'message' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Fetch news from a specific source
     */
    public function fetchFromSource(NewsSource $source): array
    {
        $method = 'fetchFrom' . ucfirst($source->slug);

        if (!method_exists($this, $method)) {
            throw new \Exception("No fetch method found for source: {$source->slug}");
        }

        $result = $this->$method($source);
        $source->markAsFetched();

        return $result;
    }

    /**
     * Fetch from NewsAPI.org
     */
    protected function fetchFromNewsapi(NewsSource $source): array
    {
        $apiKey = $source->api_key;
        $baseUrl = $source->api_url;

        $categories = Category::active()->get();
        $totalCount = 0;

        foreach ($categories as $category) {
            $response = Http::timeout(30)->get($baseUrl . '/v2/everything', [
                'apiKey' => $apiKey,
                'q' => $category->name,
                'language' => $source->language,
                'sortBy' => 'publishedAt',
                'pageSize' => 100,
                'from' => Carbon::now()->subHours(24)->toISOString(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $count = $this->processNewsApiArticles($data['articles'] ?? [], $source, $category);
                $totalCount += $count;
            }
        }

        return [
            'success' => true,
            'count' => $totalCount,
            'message' => "Fetched {$totalCount} articles from NewsAPI"
        ];
    }

    /**
     * Fetch from The Guardian API
     */
    protected function fetchFromGuardian(NewsSource $source): array
    {
        $apiKey = $source->api_key;
        $baseUrl = $source->api_url;

        $categories = Category::active()->get();
        $totalCount = 0;

        foreach ($categories as $category) {
            $response = Http::timeout(30)->get($baseUrl . '/search', [
                'api-key' => $apiKey,
                'q' => $category->name,
                'section' => $this->mapCategoryToGuardianSection($category->name),
                'show-fields' => 'headline,trailText,thumbnail,body',
                'page-size' => 50,
                'from-date' => Carbon::now()->subHours(24)->format('Y-m-d'),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $count = $this->processGuardianArticles($data['response']['results'] ?? [], $source, $category);
                $totalCount += $count;
            }
        }

        return [
            'success' => true,
            'count' => $totalCount,
            'message' => "Fetched {$totalCount} articles from The Guardian"
        ];
    }

    /**
     * Fetch from New York Times API
     */
    protected function fetchFromNytimes(NewsSource $source): array
    {
        $apiKey = $source->api_key;
        $baseUrl = $source->api_url;

        $categories = Category::active()->get();
        $totalCount = 0;

        foreach ($categories as $category) {
            $response = Http::timeout(30)->get($baseUrl . '/svc/search/v2/articlesearch.json', [
                'api-key' => $apiKey,
                'q' => $category->name,
                'fq' => $this->mapCategoryToNYTSection($category->name),
                'begin_date' => Carbon::now()->subHours(24)->format('Ymd'),
                'sort' => 'newest',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $count = $this->processNYTArticles($data['response']['docs'] ?? [], $source, $category);
                $totalCount += $count;
            }
        }

        return [
            'success' => true,
            'count' => $totalCount,
            'message' => "Fetched {$totalCount} articles from New York Times"
        ];
    }

    /**
     * Fetch from BBC News API
     */
    protected function fetchFromBbc(NewsSource $source): array
    {
        $baseUrl = $source->api_url;

        $categories = Category::active()->get();
        $totalCount = 0;

        foreach ($categories as $category) {
            $response = Http::timeout(30)->get($baseUrl . '/news', [
                'q' => $category->name,
                'language' => $source->language,
                'sortBy' => 'publishedAt',
                'pageSize' => 50,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $count = $this->processBBCArticles($data['articles'] ?? [], $source, $category);
                $totalCount += $count;
            }
        }

        return [
            'success' => true,
            'count' => $totalCount,
            'message' => "Fetched {$totalCount} articles from BBC News"
        ];
    }

    /**
     * Process NewsAPI articles
     */
    protected function processNewsApiArticles(array $articles, NewsSource $source, Category $category): int
    {
        $count = 0;

        foreach ($articles as $article) {
            if ($this->saveArticle($article, $source, $category, 'newsapi')) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Process Guardian articles
     */
    protected function processGuardianArticles(array $articles, NewsSource $source, Category $category): int
    {
        $count = 0;

        foreach ($articles as $article) {
            $processedArticle = [
                'title' => $article['webTitle'] ?? '',
                'description' => $article['fields']['trailText'] ?? '',
                'url' => $article['webUrl'] ?? '',
                'image_url' => $article['fields']['thumbnail'] ?? null,
                'author' => null,
                'published_at' => Carbon::parse($article['webPublicationDate'] ?? now()),
                'external_id' => $article['id'] ?? null,
                'metadata' => $article
            ];

            if ($this->saveArticle($processedArticle, $source, $category, 'guardian')) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Process NYT articles
     */
    protected function processNYTArticles(array $articles, NewsSource $source, Category $category): int
    {
        $count = 0;

        foreach ($articles as $article) {
            $processedArticle = [
                'title' => $article['headline']['main'] ?? '',
                'description' => $article['abstract'] ?? '',
                'url' => $article['web_url'] ?? '',
                'image_url' => $this->getNYTImageUrl($article),
                'author' => $article['byline']['original'] ?? null,
                'published_at' => Carbon::parse($article['pub_date'] ?? now()),
                'external_id' => $article['_id'] ?? null,
                'metadata' => $article
            ];

            if ($this->saveArticle($processedArticle, $source, $category, 'nytimes')) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Process BBC articles
     */
    protected function processBBCArticles(array $articles, NewsSource $source, Category $category): int
    {
        $count = 0;

        foreach ($articles as $article) {
            if ($this->saveArticle($article, $source, $category, 'bbc')) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Save article to database
     */
    protected function saveArticle(array $article, NewsSource $source, Category $category, string $sourceType): bool
    {
        try {
            // Check if article already exists
            $existingArticle = News::where('url', $article['url'])
                                 ->orWhere('external_id', $article['external_id'])
                                 ->first();

            if ($existingArticle) {
                return false; // Skip duplicate
            }

            News::create([
                'title' => $article['title'],
                'description' => $article['description'],
                'content' => $article['content'] ?? null,
                'url' => $article['url'],
                'image_url' => $article['image_url'],
                'author' => $article['author'],
                'published_at' => $article['published_at'],
                'news_source_id' => $source->id,
                'category_id' => $category->id,
                'external_id' => $article['external_id'],
                'metadata' => $article['metadata'] ?? null,
                'is_active' => true
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to save article: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Map category to Guardian section
     */
    protected function mapCategoryToGuardianSection(string $category): string
    {
        $mapping = [
            'Technology' => 'technology',
            'Business' => 'business',
            'Sports' => 'sport',
            'Health' => 'society',
            'Science' => 'science',
            'Politics' => 'politics',
            'World' => 'world',
            'Entertainment' => 'culture'
        ];

        return $mapping[$category] ?? 'news';
    }

    /**
     * Map category to NYT section
     */
    protected function mapCategoryToNYTSection(string $category): string
    {
        $mapping = [
            'Technology' => 'technology',
            'Business' => 'business',
            'Sports' => 'sports',
            'Health' => 'health',
            'Science' => 'science',
            'Politics' => 'politics',
            'World' => 'world',
            'Entertainment' => 'arts'
        ];

        return "section_name:(\"{$mapping[$category]}\")" ?? 'news';
    }

    /**
     * Get NYT image URL
     */
    protected function getNYTImageUrl(array $article): ?string
    {
        if (isset($article['multimedia']) && is_array($article['multimedia'])) {
            foreach ($article['multimedia'] as $media) {
                if ($media['type'] === 'image' && $media['subtype'] === 'large') {
                    return 'https://www.nytimes.com/' . $media['url'];
                }
            }
        }
        return null;
    }
}
