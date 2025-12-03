<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends ApiController
{
    /**
     * Get all users (Admin only)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $users = User::paginate(15);

            return $this->success(
                UserResource::collection($users),
                'Users retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Get single user (Admin only)
     */
    public function show(User $user): JsonResponse
    {
        return $this->success(
            new UserResource($user),
            'User retrieved successfully'
        );
    }

    /**
     * Create user (Admin only)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/[A-Z]/',
                    'regex:/[a-z]/',
                    'regex:/[0-9]/',
                ],
                'role' => ['required', 'in:user,admin'],
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
            ]);

            return $this->created(
                new UserResource($user),
                'User created successfully'
            );
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Update user (Admin only)
     */
    public function update(Request $request, User $user): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'email' => ['sometimes', 'email', 'unique:users,email,' . $user->id],
                'role' => ['sometimes', 'in:user,admin'],
            ]);

            if (isset($validated['name'])) {
                $user->name = $validated['name'];
            }

            if (isset($validated['email'])) {
                $user->email = $validated['email'];
            }

            if (isset($validated['role'])) {
                if ($validated['role'] === 'user' && $user->isAdmin()) {
                    $adminCount = User::where('role', 'admin')->count();
                    if ($adminCount <= 1) {
                        return $this->error(
                            'Cannot demote the last admin user',
                            400
                        );
                    }
                }

                $user->role = $validated['role'];
            }

            $user->save();

            return $this->success(
                new UserResource($user),
                'User updated successfully'
            );
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Delete user (Admin only)
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            // Prevent deleting the last admin
            if ($user->isAdmin()) {
                $adminCount = User::where('role', 'admin')->count();
                if ($adminCount <= 1) {
                    return $this->error(
                        'Cannot delete the last admin user',
                        400
                    );
                }
            }

            $user->delete();

            return $this->success(
                null,
                'User deleted successfully'
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
