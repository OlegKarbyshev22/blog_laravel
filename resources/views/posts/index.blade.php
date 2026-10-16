@extends('layouts.app')
@section('title', 'Главная')
@section('content')
<section class="hero">
    <div class="container">
        <div class="row align-items-end g-4">
            <div class="col-lg-8">
                <div class="text-accent fw-bold text-uppercase mb-3">Люди • идеи • технологии</div>
                <h1>Истории, которые хочется обсудить.</h1>
                <p class="lead text-white-50 mt-3 mb-0">Авторские материалы, честные оценки и живой разговор.</p>
            </div>
            <div class="col-lg-4">
                <form class="bg-white p-2 rounded-3 d-flex" action="{{ route('posts.index') }}">
                    <input class="form-control border-0" name="search" value="{{ request('search') }}" placeholder="Найти публикацию">
                    <button class="btn btn-accent">Найти</button>
                </form>
            </div>
        </div>
    </div>
</section>

<div class="container py-5">
    <div class="d-flex gap-2 flex-wrap mb-4">
        <a href="{{ route('posts.index', request()->only('search')) }}" class="btn btn-sm {{ request('category') ? 'btn-outline-secondary' : 'btn-dark' }}">Все темы</a>
        @foreach($categories as $category)
            <a href="{{ route('posts.index', array_filter(['category'=>$category->slug,'search'=>request('search')])) }}" class="btn btn-sm {{ request('category') === $category->slug ? 'btn-dark' : 'btn-outline-secondary' }}">{{ $category->name }}</a>
        @endforeach
    </div>

    <div class="row g-4">
        @forelse($posts as $post)
            <div class="col-md-6 col-lg-4">
                <article class="card post-card">
                    @if($post->image_url)<img src="{{ $post->image_url }}" alt="">@else<div class="placeholder-cover"></div>@endif
                    <div class="card-body p-4 d-flex flex-column">
                        <div><span class="badge badge-category">{{ $post->category->name }}</span></div>
                        <h2 class="h4 fw-bold mt-3"><a class="text-decoration-none text-dark" href="{{ route('posts.show', $post) }}">{{ $post->title }}</a></h2>
                        <p class="text-secondary">{{ $post->excerpt }}</p>
                        <div class="mt-auto small text-secondary d-flex justify-content-between">
                            <span>{{ $post->author->name }} · {{ $post->published_at->format('d.m.Y') }}</span>
                            <span class="star">★ {{ $post->ratings_avg_value ? number_format($post->ratings_avg_value, 1) : '—' }}</span>
                        </div>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12"><div class="alert alert-light border">По вашему запросу публикаций не найдено.</div></div>
        @endforelse
    </div>
    <div class="mt-5">{{ $posts->links() }}</div>
</div>
@endsection
