<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsSource;
use App\Services\ApiResponseService;
use Exception;

class NewsSourceController extends Controller
{
    protected $apiResponseService;

    public function __construct(ApiResponseService $apiResponseService)
    {
        $this->apiResponseService = $apiResponseService;
    }

    /**
     * Get all active news sources
     */
    public function index()
    {
        try {
            $sources = NewsSource::active()
                                ->orderBy('name')
                                ->get();

            return $this->apiResponseService->success([
                'sources' => $sources
            ], 'News sources retrieved successfully');

        } catch (Exception $e) {
            return $this->apiResponseService->error(
                'Failed to retrieve news sources: ' . $e->getMessage()
            );
        }
    }

    /**
     * Get a specific news source
     */
    public function show(string $slug)
    {
        try {
            $source = NewsSource::where('slug', $slug)
                               ->active()
                               ->firstOrFail();

            return $this->apiResponseService->success([
                'source' => $source
            ], 'News source retrieved successfully');

        } catch (Exception $e) {
            return $this->apiResponseService->error(
                'Failed to retrieve news source: ' . $e->getMessage(),
                null,
                404
            );
        }
    }
}
