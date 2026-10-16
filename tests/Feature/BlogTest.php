<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Enrollment;
use App\Models\Post;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    private function createPost(string $status = 'published'): Post
    {
        $user = User::factory()->create();
        $category = Category::firstOrCreate(['slug' => 'test'], ['name' => 'Тест']);
        $slug = 'test-post-'.str()->random(6);

        return Post::create([
            'user_id' => $user->id, 'category_id' => $category->id,
            'title' => 'Тестовая статья', 'slug' => $slug,
            'excerpt' => 'Краткое описание', 'body' => 'Достаточно длинный текст публикации.',
            'status' => $status, 'published_at' => $status === 'published' ? now() : null,
        ]);
    }

    public function test_only_published_posts_are_visible(): void
    {
        $published = $this->createPost();
        $draft = $this->createPost('draft');
        $draft->update(['slug' => 'draft-post', 'title' => 'Скрытый черновик']);

        $this->get('/')->assertOk()->assertSee($published->title)->assertDontSee($draft->title);
        $this->get(route('posts.show', $draft))->assertNotFound();
    }

    public function test_authenticated_user_can_comment_and_rate_once(): void
    {
        $post = $this->createPost();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('comments.store', $post), ['body' => 'Комментарий на проверку'])->assertRedirect();
        $this->assertDatabaseHas('comments', ['user_id' => $user->id, 'status' => 'pending']);

        $this->actingAs($user)->post(route('ratings.store', $post), ['value' => 3]);
        $this->actingAs($user)->post(route('ratings.store', $post), ['value' => 5]);
        $this->assertSame(1, Rating::where('user_id', $user->id)->count());
        $this->assertDatabaseHas('ratings', ['user_id' => $user->id, 'value' => 5]);
    }

    public function test_admin_can_moderate_comment_but_regular_user_cannot(): void
    {
        $post = $this->createPost();
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $comment = Comment::create(['post_id' => $post->id, 'user_id' => $user->id, 'body' => 'Текст', 'status' => 'pending']);

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Панель администратора');
        $this->actingAs($admin)->patch(route('admin.comments.update', $comment), ['status' => 'approved'])->assertRedirect();
        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'status' => 'approved', 'moderated_by' => $admin->id]);
    }

    public function test_no_more_than_three_users_can_enroll_in_one_topic(): void
    {
        $post = $this->createPost();
        $users = User::factory()->count(4)->create();

        foreach ($users->take(3) as $user) {
            $this->actingAs($user)
                ->post(route('enrollments.store', $post))
                ->assertRedirect();
        }

        $this->actingAs($users->last())
            ->post(route('enrollments.store', $post))
            ->assertSessionHasErrors('enrollment');

        $this->assertSame(3, Enrollment::where('post_id', $post->id)->where('status', 'confirmed')->count());
        $this->assertDatabaseMissing('enrollments', ['post_id' => $post->id, 'user_id' => $users->last()->id]);
    }

    public function test_user_can_cancel_enrollment_and_free_a_place(): void
    {
        $post = $this->createPost();
        $users = User::factory()->count(4)->create();

        foreach ($users->take(3) as $user) {
            $this->actingAs($user)->post(route('enrollments.store', $post));
        }

        $this->actingAs($users->first())
            ->delete(route('enrollments.destroy', $post))
            ->assertRedirect();

        $this->actingAs($users->last())
            ->post(route('enrollments.store', $post))
            ->assertSessionDoesntHaveErrors();

        $this->assertSame(3, Enrollment::where('post_id', $post->id)->where('status', 'confirmed')->count());
    }

    public function test_admin_can_manage_enrollments(): void
    {
        $post = $this->createPost();
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $enrollment = Enrollment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'status' => 'confirmed',
        ]);

        $this->actingAs($admin)->get(route('admin.enrollments.index'))->assertOk()->assertSee($user->email);
        $this->actingAs($admin)->patch(route('admin.enrollments.update', $enrollment), ['status' => 'cancelled'])->assertRedirect();
        $this->assertDatabaseHas('enrollments', ['id' => $enrollment->id, 'status' => 'cancelled']);
        $this->actingAs($admin)->delete(route('admin.enrollments.destroy', $enrollment))->assertRedirect();
        $this->assertDatabaseMissing('enrollments', ['id' => $enrollment->id]);
    }
}
