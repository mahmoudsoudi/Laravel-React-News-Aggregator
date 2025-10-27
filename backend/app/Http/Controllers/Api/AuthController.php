<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\ApiResponseService;
use App\Services\AuthService;
use Exception;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        try {
            $data = $this->authService->register($request->validated());

            return ApiResponseService::success(
                $data,
                'User registered successfully',
                201
            );
        } catch (Exception $e) {
            return ApiResponseService::error(
                'Registration failed: ' . $e->getMessage()
            );
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $data = $this->authService->login($request->validated());

            return ApiResponseService::success(
                $data,
                'Login successful'
            );
        } catch (Exception $e) {
            return ApiResponseService::error(
                $e->getMessage(),
                null,
                $e->getCode() ?: 401
            );
        }
    }

    public function logout()
    {
        try {
            $this->authService->logout(auth()->user());

            return ApiResponseService::success(
                null,
                'Logged out successfully'
            );
        } catch (Exception $e) {
            return ApiResponseService::error(
                'Logout failed: ' . $e->getMessage()
            );
        }
    }
}
