@extends('layouts.app')
@section('title', $post->title)
@section('content')
<article class="container py-5">
    <div class="mx-auto" style="max-width:900px">
        <a href="{{ route('posts.index', ['category' => $post->category->slug]) }}" class="badge badge-category text-decoration-none mb-3">{{ $post->category->name }}</a>
        <h1 class="display-4 fw-bold">{{ $post->title }}</h1>
        <div class="text-secondary mb-4">
            {{ $post->author->name }} · {{ $post->published_at->format('d.m.Y, H:i') }} ·
            <span class="star">★ {{ $post->ratings_avg_value ? number_format($post->ratings_avg_value, 1) : 'нет оценок' }}</span>
            ({{ $post->ratings_count }})
        </div>
        @if($post->image_url)<img class="article-cover mb-5" src="{{ $post->image_url }}" alt="{{ $post->title }}">@endif
        <div class="article-body">{{ $post->body }}</div>

        <section class="card form-card p-4 mt-5 enrollment-card">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h2 class="h4 fw-bold mb-1">Запись на тему</h2>
                    <p class="mb-0 text-secondary">
                        Подтверждено: {{ $post->confirmed_enrollments_count }} из {{ \App\Models\Enrollment::MAX_PER_POST }}.
                        Свободных мест: <strong>{{ $availableSlots }}</strong>.
                    </p>
                </div>
                @auth
                    @if($userEnrollment?->status === 'confirmed')
                        <form action="{{ route('enrollments.destroy', $post) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger">Отменить мою запись</button>
                        </form>
                    @elseif($availableSlots > 0)
                        <form action="{{ route('enrollments.store', $post) }}" method="POST">
                            @csrf
                            <button class="btn btn-accent">Записаться</button>
                        </form>
                    @else
                        <span class="badge text-bg-secondary fs-6">Мест нет</span>
                    @endif
                @else
                    <a class="btn btn-accent" href="{{ route('login') }}">Войти и записаться</a>
                @endauth
            </div>
            @error('enrollment')<div class="alert alert-danger mt-3 mb-0">{{ $message }}</div>@enderror
        </section>

        <section class="card form-card p-4 mt-4">
            <h2 class="h4 fw-bold">Ваша оценка</h2>
            @auth
                <form action="{{ route('ratings.store', $post) }}" method="POST" class="d-flex flex-wrap align-items-center gap-2">@csrf
                    @for($i = 1; $i <= 5; $i++)
                        <button name="value" value="{{ $i }}" class="btn {{ $userRating === $i ? 'btn-warning' : 'btn-outline-warning' }}">{{ $i }} ★</button>
                    @endfor
                </form>
            @else
                <p class="mb-0"><a href="{{ route('login') }}">Войдите</a>, чтобы поставить оценку.</p>
            @endauth
        </section>

        <section class="mt-5">
            <h2 class="h3 fw-bold">Комментарии <span class="text-secondary">{{ $post->comments->count() }}</span></h2>
            @auth
                <form class="card form-card p-4 my-4" action="{{ route('comments.store', $post) }}" method="POST">@csrf
                    <label class="form-label fw-bold" for="body">Присоединиться к обсуждению</label>
                    <textarea class="form-control @error('body') is-invalid @enderror" id="body" name="body" rows="4" required>{{ old('body') }}</textarea>
                    @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text mb-3">Комментарий появится после проверки администратором.</div>
                    <button class="btn btn-accent align-self-start">Отправить</button>
                </form>
            @else
                <div class="alert alert-light border my-4"><a href="{{ route('login') }}">Войдите</a>, чтобы оставить комментарий.</div>
            @endauth
            @forelse($post->comments as $comment)
                <div class="border-bottom py-4">
                    <div class="fw-bold">{{ $comment->user->name }} <span class="fw-normal text-secondary small">· {{ $comment->created_at->diffForHumans() }}</span></div>
                    <div class="mt-2">{{ $comment->body }}</div>
                </div>
            @empty
                <p class="text-secondary">Одобренных комментариев пока нет. Начните обсуждение.</p>
            @endforelse
        </section>
    </div>
</article>
@endsection
