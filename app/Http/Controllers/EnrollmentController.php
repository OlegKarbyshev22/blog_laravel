<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnrollmentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        abort_unless($post->status === 'published' && $post->published_at?->isPast(), 404);

        DB::transaction(function () use ($request, $post) {
            $lockedPost = Post::query()->lockForUpdate()->findOrFail($post->id);
            $existing = Enrollment::query()
                ->where('post_id', $lockedPost->id)
                ->where('user_id', $request->user()->id)
                ->first();

            if ($existing?->status === 'confirmed') {
                return;
            }

            $occupied = Enrollment::query()
                ->where('post_id', $lockedPost->id)
                ->where('status', 'confirmed')
                ->count();

            if ($occupied >= Enrollment::MAX_PER_POST) {
                throw ValidationException::withMessages([
                    'enrollment' => 'На эту тему уже записались 3 человека. Свободных мест нет.',
                ]);
            }

            Enrollment::updateOrCreate(
                ['post_id' => $lockedPost->id, 'user_id' => $request->user()->id],
                ['status' => 'confirmed'],
            );
        }, 3);

        return back()->with('success', 'Вы записались на тему.');
    }

    public function destroy(Request $request, Post $post)
    {
        Enrollment::query()
            ->where('post_id', $post->id)
            ->where('user_id', $request->user()->id)
            ->delete();

        return back()->with('success', 'Запись на тему отменена.');
    }
}
