<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ApiResponseService;
use Exception;

class CategoryController extends Controller
{
    protected $apiResponseService;

    public function __construct(ApiResponseService $apiResponseService)
    {
        $this->apiResponseService = $apiResponseService;
    }

    /**
     * Get all active categories
     */
    public function index()
    {
        try {
            $categories = Category::active()
                                ->ordered()
                                ->get();

            return $this->apiResponseService->success([
                'categories' => $categories
            ], 'Categories retrieved successfully');

        } catch (Exception $e) {
            return $this->apiResponseService->error(
                'Failed to retrieve categories: ' . $e->getMessage()
            );
        }
    }

    /**
     * Get a specific category
     */
    public function show(string $slug)
    {
        try {
            $category = Category::where('slug', $slug)
                              ->active()
                              ->firstOrFail();

            return $this->apiResponseService->success([
                'category' => $category
            ], 'Category retrieved successfully');

        } catch (Exception $e) {
            return $this->apiResponseService->error(
                'Failed to retrieve category: ' . $e->getMessage(),
                null,
                404
            );
        }
    }
}
