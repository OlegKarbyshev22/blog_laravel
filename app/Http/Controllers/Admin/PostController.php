<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::with(['author', 'category'])->withCount('comments')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()->paginate(10)->withQueryString();

        return view('admin.posts.index', compact('posts'));
    }

    public function create()
    {
        return view('admin.posts.create', ['categories' => Category::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['published_at'] = $data['status'] === 'published' ? ($data['published_at'] ?? now()) : null;
        Post::create($data);

        return redirect()->route('admin.posts.index')->with('success', 'Публикация создана.');
    }

    public function show(Post $post)
    {
        return view('admin.posts.show', compact('post'));
    }

    public function edit(Post $post)
    {
        return view('admin.posts.edit', ['post' => $post, 'categories' => Category::orderBy('name')->get()]);
    }

    public function update(Request $request, Post $post)
    {
        $data = $this->validated($request);
        if ($post->title !== $data['title']) {
            $data['slug'] = $this->uniqueSlug($data['title'], $post->id);
        }
        $data['published_at'] = $data['status'] === 'published' ? ($data['published_at'] ?? $post->published_at ?? now()) : null;
        $post->update($data);

        return redirect()->route('admin.posts.index')->with('success', 'Публикация обновлена.');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'Публикация удалена.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:200'],
            'excerpt' => ['required', 'string', 'max:1000'],
            'body' => ['required', 'string', 'min:20'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
        ]);
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'post';
        $slug = $base;
        $i = 2;
        while (Post::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
