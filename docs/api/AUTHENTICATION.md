# Authentication & Authorization

## Overview

API sử dụng Laravel Sanctum cho token-based authentication.

## Setup Sanctum

### 1. Install & Configure

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### 2. Add HasApiTokens to User Model

```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
}
```

### 3. Configure Middleware

Thêm vào `app/Http/Kernel.php`:
```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

## Authentication Flow

### 1. Login Endpoint

```php
// app/Http/Controllers/Api/V1/AuthController.php
public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (!Auth::attempt($request->only('email', 'password'))) {
        return $this->unauthorized('Invalid credentials');
    }

    $user = Auth::user();
    $token = $user->createToken('api-token')->plainTextToken;

    return $this->success([
        'user' => new UserResource($user),
        'token' => $token,
    ], 'Login successful', 200);
}
```

### 2. Protected Routes

```php
// routes/api/v1.php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $r) => new UserResource($r->user()));
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('users', UserController::class);
});
```

### 3. Client Usage

```javascript
// Frontend - Attach token to requests
const token = localStorage.getItem('api_token');

fetch('/api/v1/users', {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
});
```

## Token Management

### Generate Token

```php
$token = $user->createToken('api-token')->plainTextToken;

// With abilities
$token = $user->createToken('api-token', ['read', 'create'])->plainTextToken;
```

### Revoke Token

```php
$user->tokens()->delete();

// Or specific token
$user->tokens->where('name', 'api-token')->first()->delete();
```

### Check Token Abilities

```php
// In controller
if ($request->user()->tokenCan('read')) {
    // User can read
}
```

## Protected Resources

### Controller Example

```php
class UserController extends ApiController
{
    public function show(User $user, Request $request)
    {
        // Only allow viewing own profile unless admin
        if ($request->user()->id !== $user->id && !$request->user()->is_admin) {
            return $this->forbidden('Cannot view other profiles');
        }

        return $this->success(new UserResource($user));
    }
}
```

## Token Expiration

### Configure in config/sanctum.php

```php
'expiration' => 60 * 24 * 365, // 1 year in minutes
```

### Refresh Token Pattern

Implement refresh token untuk security:

```php
// Issue refresh token
$refreshToken = $user->createToken('refresh-token', ['refresh'])->plainTextToken;

// Use refresh token to get new access token
public function refresh(Request $request)
{
    $token = $request->user()->createToken('api-token')->plainTextToken;
    return $this->success(['token' => $token]);
}
```

## Authorization (Gates & Policies)

### Define Gates

```php
// app/Providers/AuthServiceProvider.php
use Illuminate\Support\Facades\Gate;

Gate::define('update-post', function (User $user, Post $post) {
    return $user->id === $post->user_id;
});
```

### Use in Controller

```php
public function update(Request $request, Post $post)
{
    if (!Gate::allows('update-post', $post)) {
        return $this->forbidden();
    }

    $post->update($request->validated());
    return $this->success(new PostResource($post));
}
```

### Or Use Authorize

```php
public function update(Request $request, Post $post)
{
    $this->authorize('update-post', $post);

    $post->update($request->validated());
    return $this->success(new PostResource($post));
}
```

## Error Responses

### Unauthorized (401)

```json
{
  "success": false,
  "message": "Unauthorized",
  "errors": null
}
```

### Forbidden (403)

```json
{
  "success": false,
  "message": "Forbidden",
  "errors": null
}
```

## Best Practices

1. **Always use HTTPS** in production
2. **Store tokens securely** (not in localStorage)
3. **Implement token refresh** for long-lived sessions
4. **Set appropriate expiration** times
5. **Revoke tokens on logout**
6. **Use abilities** for fine-grained permissions
7. **Log authentication** failures
8. **Rate limit** login attempts
9. **Validate email** before allowing login
10. **Use strong passwords** requirements

## Testing

```php
// tests/Feature/AuthTest.php
public function test_user_can_login()
{
    $user = User::factory()->create();

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertStatus(200);
    $response->assertHasJsonPath('data.token');
}

public function test_protected_endpoint_requires_token()
{
    $response = $this->getJson('/api/v1/user');
    $response->assertUnauthorized();
}

public function test_authenticated_user_can_access()
{
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->getJson('/api/v1/user', [
        'Authorization' => "Bearer $token",
    ]);

    $response->assertOk();
}
```
