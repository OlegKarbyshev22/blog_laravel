<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request, Post $post)
    {
        abort_unless($post->status === 'published', 404);
        $data = $request->validate(['value' => ['required', 'integer', 'between:1,5']]);
        $post->ratings()->updateOrCreate(['user_id' => $request->user()->id], $data);

        return back()->with('success', 'Ваша оценка сохранена.');
    }
}
