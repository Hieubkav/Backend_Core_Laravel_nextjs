<?php

namespace App\Services;

use App\Models\Post;
use App\Repositories\PostRepository;
use Illuminate\Support\Str;

class PostService extends BaseService
{
    public function __construct(private PostRepository $posts)
    {
    }

    /**
     * Get paginated posts.
     */
    public function list(int $perPage = 15)
    {
        return $this->posts->paginate($perPage);
    }

    /**
     * Create a new post.
     */
    public function create(array $data): Post
    {
        $data['slug'] ??= Str::slug($data['title']);

        return $this->posts->create($data);
    }

    /**
     * Get a post by id.
     */
    public function find(int|string $id): Post
    {
        return $this->posts->findOrFail($id);
    }

    /**
     * Update a post.
     */
    public function update(Post $post, array $data): Post
    {
        $this->posts->update($post->id, $data);

        return $post->refresh();
    }

    /**
     * Delete a post.
     */
    public function delete(Post $post): bool
    {
        return $this->posts->delete($post->id);
    }
}
