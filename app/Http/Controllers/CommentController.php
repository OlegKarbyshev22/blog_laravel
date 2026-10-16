<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        abort_unless($post->status === 'published', 404);
        $data = $request->validate(['body' => ['required', 'string', 'min:3', 'max:2000']]);
        $post->comments()->create($data + ['user_id' => $request->user()->id, 'status' => 'pending']);

        return back()->with('success', 'Комментарий отправлен на модерацию.');
    }
}
