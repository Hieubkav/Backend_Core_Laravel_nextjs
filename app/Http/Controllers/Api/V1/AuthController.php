<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends ApiController
{
    public function __construct(private AuthService $authService)
    {
    }

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->register($request->validated());

            return $this->created(
                new UserResource($user),
                'User registered successfully'
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login(
                $request->email,
                $request->password
            );

            return $this->success(
                [
                    'user' => new UserResource($result['user']),
                    'access_token' => $result['access_token'],
                    'token_type' => $result['token_type'],
                ],
                'Login successful'
            );
        } catch (ValidationException $e) {
            return $this->error(
                'Invalid credentials',
                401,
                $e->errors()
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        return $this->success(
            new UserResource($request->user()),
            'User profile retrieved'
        );
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $request->user()->id,
            ]);

            $user = $this->authService->updateProfile($request->user(), $validated);

            return $this->success(
                new UserResource($user),
                'Profile updated successfully'
            );
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Change password
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->changePassword(
                $request->user(),
                $request->current_password,
                $request->new_password
            );

            return $this->success(
                null,
                'Password changed successfully'
            );
        } catch (ValidationException $e) {
            return $this->error(
                'Password change failed',
                400,
                $e->errors()
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout($request->user());

            return $this->success(
                null,
                'Logged out successfully'
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
