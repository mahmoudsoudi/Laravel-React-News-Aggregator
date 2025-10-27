<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\ApiResponseService;
use App\Services\UserService;
use Exception;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function profile()
    {
        try {
            $user = $this->userService->getProfile(auth()->user());

            return ApiResponseService::success([
                'user' => $user
            ]);
        } catch (Exception $e) {
            return ApiResponseService::error(
                'Failed to fetch profile: ' . $e->getMessage()
            );
        }
    }

    public function update(UpdateProfileRequest $request)
    {
        try {
            $user = $this->userService->updateProfile(
                auth()->user(),
                $request->validated()
            );

            return ApiResponseService::success([
                'user' => $user
            ], 'Profile updated successfully');
        } catch (Exception $e) {
            return ApiResponseService::error(
                'Failed to update profile: ' . $e->getMessage()
            );
        }
    }

    public function delete()
    {
        try {
            $this->userService->deleteAccount(auth()->user());

            return ApiResponseService::success(
                null,
                'Account deleted successfully'
            );
        } catch (Exception $e) {
            return ApiResponseService::error(
                'Failed to delete account: ' . $e->getMessage()
            );
        }
    }
}
