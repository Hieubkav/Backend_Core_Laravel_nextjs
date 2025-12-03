<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Post\PostStoreRequest;
use App\Http\Requests\Post\PostUpdateRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends ApiController
{
    public function __construct(private PostService $postService)
    {
    }

    /**
     * List posts.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->get('per_page', 15);
            $posts = $this->postService->list($perPage);

            return $this->success(
                PostResource::collection($posts),
                'Posts retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Create a post.
     */
    public function store(PostStoreRequest $request): JsonResponse
    {
        try {
            $post = $this->postService->create($request->validated());

            return $this->created(
                new PostResource($post),
                'Post created successfully'
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Show a post.
     */
    public function show(Post $post): JsonResponse
    {
        return $this->success(
            new PostResource($post),
            'Post retrieved successfully'
        );
    }

    /**
     * Update a post.
     */
    public function update(PostUpdateRequest $request, Post $post): JsonResponse
    {
        try {
            $updated = $this->postService->update($post, $request->validated());

            return $this->success(
                new PostResource($updated),
                'Post updated successfully'
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Delete a post.
     */
    public function destroy(Post $post): JsonResponse
    {
        try {
            $this->postService->delete($post);

            return $this->success(
                null,
                'Post deleted successfully'
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
