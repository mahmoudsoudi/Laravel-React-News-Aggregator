<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserPreference;
use App\Models\NewsSource;
use App\Models\Category;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Exception;

class UserPreferenceController extends Controller
{
    protected $apiResponseService;

    public function __construct(ApiResponseService $apiResponseService)
    {
        $this->apiResponseService = $apiResponseService;
    }

    /**
     * Get user preferences
     */
    public function index()
    {
        try {
            $user = auth()->user();
            $preferences = $user->getPreferences();

            return $this->apiResponseService->success([
                'preferences' => $preferences,
                'available_sources' => NewsSource::active()->get(),
                'available_categories' => Category::active()->ordered()->get()
            ], 'User preferences retrieved successfully');

        } catch (Exception $e) {
            return $this->apiResponseService->error(
                'Failed to retrieve user preferences: ' . $e->getMessage()
            );
        }
    }

    /**
     * Update user preferences
     */
    public function update(Request $request)
    {
        try {
            $user = auth()->user();
            $preferences = $user->getPreferences();

            $validated = $request->validate([
                'preferred_sources' => 'nullable|array',
                'preferred_sources.*' => 'exists:news_sources,id',
                'preferred_categories' => 'nullable|array',
                'preferred_categories.*' => 'exists:categories,id',
                'excluded_sources' => 'nullable|array',
                'excluded_sources.*' => 'exists:news_sources,id',
                'excluded_categories' => 'nullable|array',
                'excluded_categories.*' => 'exists:categories,id',
                'language' => 'nullable|string|max:5',
                'country' => 'nullable|string|max:255',
                'items_per_page' => 'nullable|integer|min:5|max:100',
                'show_images' => 'nullable|boolean',
                'auto_refresh' => 'nullable|boolean',
                'refresh_interval_minutes' => 'nullable|integer|min:5|max:1440',
                'notification_settings' => 'nullable|array'
            ]);

            $preferences->update($validated);

            return $this->apiResponseService->success([
                'preferences' => $preferences->fresh()
            ], 'User preferences updated successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return $this->apiResponseService->error(
                'Failed to update user preferences: ' . $e->getMessage()
            );
        }
    }

    /**
     * Add preferred source
     */
    public function addPreferredSource(Request $request)
    {
        try {
            $user = auth()->user();
            $preferences = $user->getPreferences();

            $request->validate([
                'source_id' => 'required|exists:news_sources,id'
            ]);

            $preferences->addPreferredSource($request->source_id);

            return $this->apiResponseService->success([
                'preferences' => $preferences->fresh()
            ], 'Preferred source added successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return $this->apiResponseService->error(
                'Failed to add preferred source: ' . $e->getMessage()
            );
        }
    }

    /**
     * Remove preferred source
     */
    public function removePreferredSource(Request $request)
    {
        try {
            $user = auth()->user();
            $preferences = $user->getPreferences();

            $request->validate([
                'source_id' => 'required|exists:news_sources,id'
            ]);

            $preferences->removePreferredSource($request->source_id);

            return $this->apiResponseService->success([
                'preferences' => $preferences->fresh()
            ], 'Preferred source removed successfully');

        } catch (Exception $e) {
            return $this->apiResponseService->error(
                'Failed to remove preferred source: ' . $e->getMessage()
            );
        }
    }

    /**
     * Add preferred category
     */
    public function addPreferredCategory(Request $request)
    {
        try {
            $user = auth()->user();
            $preferences = $user->getPreferences();

            $request->validate([
                'category_id' => 'required|exists:categories,id'
            ]);

            $preferences->addPreferredCategory($request->category_id);

            return $this->apiResponseService->success([
                'preferences' => $preferences->fresh()
            ], 'Preferred category added successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return $this->apiResponseService->error(
                'Failed to add preferred category: ' . $e->getMessage()
            );
        }
    }

    /**
     * Remove preferred category
     */
    public function removePreferredCategory(Request $request)
    {
        try {
            $user = auth()->user();
            $preferences = $user->getPreferences();

            $request->validate([
                'category_id' => 'required|exists:categories,id'
            ]);

            $preferences->removePreferredCategory($request->category_id);

            return $this->apiResponseService->success([
                'preferences' => $preferences->fresh()
            ], 'Preferred category removed successfully');

        } catch (Exception $e) {
            return $this->apiResponseService->error(
                'Failed to remove preferred category: ' . $e->getMessage()
            );
        }
    }
}
