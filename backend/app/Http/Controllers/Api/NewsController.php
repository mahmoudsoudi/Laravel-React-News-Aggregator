<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Category;
use App\Models\NewsSource;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class NewsController extends Controller
{
    protected $apiResponseService;

    public function __construct(ApiResponseService $apiResponseService)
    {
        $this->apiResponseService = $apiResponseService;
    }

    /**
     * Get paginated news articles
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = News::with(['newsSource', 'category'])
                        ->active()
                        ->orderBy('published_at', 'desc');

            // Apply filters
            if ($request->has('category')) {
                $query->byCategory($request->category);
            }

            if ($request->has('source')) {
                $query->bySource($request->source);
            }

            if ($request->has('search')) {
                $query->search($request->search);
            }

            if ($request->has('days')) {
                $query->recent($request->days);
            }

            // Get user preferences if authenticated
            $user = auth()->user();
            if ($user && $user->preferences) {
                $preferences = $user->preferences;

                // Filter by preferred sources
                if ($preferences->preferred_sources) {
                    $query->whereIn('news_source_id', $preferences->preferred_sources);
                }

                // Filter by preferred categories
                if ($preferences->preferred_categories) {
                    $query->whereIn('category_id', $preferences->preferred_categories);
                }

                // Exclude sources
                if ($preferences->excluded_sources) {
                    $query->whereNotIn('news_source_id', $preferences->excluded_sources);
                }

                // Exclude categories
                if ($preferences->excluded_categories) {
                    $query->whereNotIn('category_id', $preferences->excluded_categories);
                }
            }

            $perPage = $request->get('per_page', 20);
            $news = $query->paginate($perPage);

            return $this->apiResponseService->success([
                'news' => $news->items(),
                'pagination' => [
                    'current_page' => $news->currentPage(),
                    'last_page' => $news->lastPage(),
                    'per_page' => $news->perPage(),
                    'total' => $news->total(),
                    'from' => $news->firstItem(),
                    'to' => $news->lastItem()
                ]
            ], 'News retrieved successfully');

        } catch (Exception $e) {
            return $this->apiResponseService->error(
                'Failed to retrieve news: ' . $e->getMessage()
            );
        }
    }

    /**
     * Get a specific news article
     */
    public function show(string $id): JsonResponse
    {
        try {
            $news = News::with(['newsSource', 'category'])
                       ->active()
                       ->findOrFail($id);

            return $this->apiResponseService->success([
                'news' => $news
            ], 'News article retrieved successfully');

        } catch (Exception $e) {
            return $this->apiResponseService->error(
                'Failed to retrieve news article: ' . $e->getMessage(),
                null,
                404
            );
        }
    }

    /**
     * Get trending news
     */
    public function trending(Request $request): JsonResponse
    {
        try {
            $query = News::with(['newsSource', 'category'])
                        ->active()
                        ->recent(24) // Last 24 hours
                        ->orderBy('published_at', 'desc');

            // Apply user preferences
            $user = auth()->user();
            if ($user && $user->preferences) {
                $preferences = $user->preferences;

                if ($preferences->preferred_sources) {
                    $query->whereIn('news_source_id', $preferences->preferred_sources);
                }

                if ($preferences->preferred_categories) {
                    $query->whereIn('category_id', $preferences->preferred_categories);
                }
            }

            $limit = $request->get('limit', 10);
            $trendingNews = $query->limit($limit)->get();

            return $this->apiResponseService->success([
                'news' => $trendingNews
            ], 'Trending news retrieved successfully');

        } catch (Exception $e) {
            return $this->apiResponseService->error(
                'Failed to retrieve trending news: ' . $e->getMessage()
            );
        }
    }

    /**
     * Get news by category
     */
    public function byCategory(string $categorySlug, Request $request): JsonResponse
    {
        try {
            $category = Category::where('slug', $categorySlug)->active()->firstOrFail();

            $query = News::with(['newsSource', 'category'])
                        ->active()
                        ->byCategory($category->id)
                        ->orderBy('published_at', 'desc');

            // Apply user preferences
            $user = auth()->user();
            if ($user && $user->preferences) {
                $preferences = $user->preferences;

                if ($preferences->preferred_sources) {
                    $query->whereIn('news_source_id', $preferences->preferred_sources);
                }
            }

            $perPage = $request->get('per_page', 20);
            $news = $query->paginate($perPage);

            return $this->apiResponseService->success([
                'category' => $category,
                'news' => $news->items(),
                'pagination' => [
                    'current_page' => $news->currentPage(),
                    'last_page' => $news->lastPage(),
                    'per_page' => $news->perPage(),
                    'total' => $news->total()
                ]
            ], "News in {$category->name} category retrieved successfully");

        } catch (Exception $e) {
            return $this->apiResponseService->error(
                'Failed to retrieve news by category: ' . $e->getMessage(),
                null,
                404
            );
        }
    }

    /**
     * Get news by source
     */
    public function bySource(string $sourceSlug, Request $request): JsonResponse
    {
        try {
            $source = NewsSource::where('slug', $sourceSlug)->active()->firstOrFail();

            $query = News::with(['newsSource', 'category'])
                        ->active()
                        ->bySource($source->id)
                        ->orderBy('published_at', 'desc');

            $perPage = $request->get('per_page', 20);
            $news = $query->paginate($perPage);

            return $this->apiResponseService->success([
                'source' => $source,
                'news' => $news->items(),
                'pagination' => [
                    'current_page' => $news->currentPage(),
                    'last_page' => $news->lastPage(),
                    'per_page' => $news->perPage(),
                    'total' => $news->total()
                ]
            ], "News from {$source->name} retrieved successfully");

        } catch (Exception $e) {
            return $this->apiResponseService->error(
                'Failed to retrieve news by source: ' . $e->getMessage(),
                null,
                404
            );
        }
    }

    /**
     * Search news
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $searchTerm = $request->get('q');

            if (!$searchTerm) {
                return $this->apiResponseService->error(
                    'Search term is required',
                    null,
                    400
                );
            }

            $query = News::with(['newsSource', 'category'])
                        ->active()
                        ->search($searchTerm)
                        ->orderBy('published_at', 'desc');

            // Apply user preferences
            $user = auth()->user();
            if ($user && $user->preferences) {
                $preferences = $user->preferences;

                if ($preferences->preferred_sources) {
                    $query->whereIn('news_source_id', $preferences->preferred_sources);
                }

                if ($preferences->preferred_categories) {
                    $query->whereIn('category_id', $preferences->preferred_categories);
                }
            }

            $perPage = $request->get('per_page', 20);
            $news = $query->paginate($perPage);

            return $this->apiResponseService->success([
                'search_term' => $searchTerm,
                'news' => $news->items(),
                'pagination' => [
                    'current_page' => $news->currentPage(),
                    'last_page' => $news->lastPage(),
                    'per_page' => $news->perPage(),
                    'total' => $news->total()
                ]
            ], "Search results for '{$searchTerm}' retrieved successfully");

        } catch (Exception $e) {
            return $this->apiResponseService->error(
                'Failed to search news: ' . $e->getMessage()
            );
        }
    }
}
