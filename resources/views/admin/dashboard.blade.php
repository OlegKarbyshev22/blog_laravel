@extends('admin.layout')
@section('title', 'Панель управления')
@section('admin-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 fw-bold mb-0">Сводка</h1>
    <a href="{{ route('admin.posts.create') }}" class="btn btn-accent">+ Новая публикация</a>
</div>
<div class="row g-3 mb-5">
    @foreach([
        ['Публикаций', $postsCount],
        ['Опубликовано', $publishedCount],
        ['На модерации', $pendingCommentsCount],
        ['Пользователей', $usersCount],
        ['Записей на темы', $enrollmentsCount],
    ] as [$label, $value])
        <div class="col-6 col-lg"><div class="card stat-card p-3"><div class="text-secondary">{{ $label }}</div><div class="display-6 fw-bold">{{ $value }}</div></div></div>
    @endforeach
</div>
<h2 class="h4 fw-bold">Последние комментарии</h2>
<div class="card table-card overflow-hidden"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Автор</th><th>Публикация</th><th>Текст</th><th>Статус</th></tr></thead>
    <tbody>
    @forelse($latestComments as $comment)
        <tr><td>{{ $comment->user->name }}</td><td>{{ $comment->post->title }}</td><td>{{ \Illuminate\Support\Str::limit($comment->body, 60) }}</td><td><span class="badge text-bg-{{ $comment->status === 'approved' ? 'success' : ($comment->status === 'rejected' ? 'danger' : 'warning') }}">{{ $comment->status }}</span></td></tr>
    @empty
        <tr><td colspan="4" class="text-center py-4">Комментариев нет</td></tr>
    @endforelse
    </tbody>
</table></div></div>
@endsection
