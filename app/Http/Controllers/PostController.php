<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Enrollment;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::query()
            ->published()
            ->with(['author', 'category'])
            ->withCount(['comments' => fn ($query) => $query->where('status', 'approved')])
            ->withAvg('ratings', 'value')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim();
                $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%"));
            })
            ->when($request->filled('category'), fn ($query) => $query->whereHas(
                'category', fn ($q) => $q->where('slug', $request->string('category'))
            ))
            ->latest('published_at')
            ->paginate(6)
            ->withQueryString();

        return view('posts.index', ['posts' => $posts, 'categories' => Category::orderBy('name')->get()]);
    }

    public function show(Post $post)
    {
        abort_unless($post->status === 'published' && $post->published_at?->isPast(), 404);

        $post->load([
            'author', 'category',
            'comments' => fn ($query) => $query->where('status', 'approved')->with('user')->latest(),
        ])->loadAvg('ratings', 'value')->loadCount([
            'ratings',
            'enrollments as confirmed_enrollments_count' => fn ($query) => $query->where('status', 'confirmed'),
        ]);

        $userRating = auth()->check()
            ? $post->ratings()->where('user_id', auth()->id())->value('value')
            : null;

        $userEnrollment = auth()->check()
            ? $post->enrollments()->where('user_id', auth()->id())->first()
            : null;

        $availableSlots = max(0, Enrollment::MAX_PER_POST - $post->confirmed_enrollments_count);

        return view('posts.show', compact('post', 'userRating', 'userEnrollment', 'availableSlots'));
    }
}
