<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Enrollment;
use App\Models\Post;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('admin.dashboard', [
            'postsCount' => Post::count(),
            'publishedCount' => Post::where('status', 'published')->count(),
            'pendingCommentsCount' => Comment::where('status', 'pending')->count(),
            'usersCount' => User::count(),
            'enrollmentsCount' => Enrollment::where('status', 'confirmed')->count(),
            'latestComments' => Comment::with(['user', 'post'])->latest()->take(5)->get(),
        ]);
    }
}
