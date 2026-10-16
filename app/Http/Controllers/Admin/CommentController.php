<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $comments = Comment::with(['user', 'post', 'moderator'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.comments.index', compact('comments'));
    }

    public function update(Request $request, Comment $comment)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['pending', 'approved', 'rejected'])]]);
        $comment->update($data + ['moderated_by' => $request->user()->id, 'moderated_at' => now()]);

        return back()->with('success', 'Статус комментария изменён.');
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();

        return back()->with('success', 'Комментарий удалён.');
    }
}
