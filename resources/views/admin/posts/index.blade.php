@extends('admin.layout')
@section('title', 'Публикации')
@section('admin-content')
<div class="d-flex flex-wrap justify-content-between gap-3 align-items-center mb-4">
    <h1 class="h2 fw-bold mb-0">Публикации</h1>
    <div class="d-flex gap-2"><form><select name="status" onchange="this.form.submit()" class="form-select"><option value="">Все статусы</option><option value="draft" @selected(request('status')==='draft')>Черновики</option><option value="published" @selected(request('status')==='published')>Опубликованные</option></select></form><a href="{{ route('admin.posts.create') }}" class="btn btn-accent text-nowrap">+ Добавить</a></div>
</div>
<div class="card table-card overflow-hidden"><div class="table-responsive"><table class="table align-middle mb-0">
<thead><tr><th>Название</th><th>Категория</th><th>Статус</th><th>Дата</th><th class="text-end">Действия</th></tr></thead>
<tbody>@forelse($posts as $post)<tr><td><strong>{{ $post->title }}</strong><div class="small text-secondary">{{ $post->author->name }} · {{ $post->comments_count }} комм.</div></td><td>{{ $post->category->name }}</td><td><span class="badge text-bg-{{ $post->status==='published'?'success':'secondary' }}">{{ $post->status==='published'?'Опубликован':'Черновик' }}</span></td><td>{{ $post->created_at->format('d.m.Y') }}</td><td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.posts.show',$post) }}">Просмотр</a> <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.posts.edit',$post) }}">Изменить</a> <form class="d-inline" method="POST" action="{{ route('admin.posts.destroy',$post) }}" onsubmit="return confirm('Удалить публикацию?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Удалить</button></form></td></tr>@empty<tr><td colspan="5" class="text-center py-4">Публикаций нет</td></tr>@endforelse</tbody>
</table></div></div><div class="mt-4">{{ $posts->links() }}</div>
@endsection
