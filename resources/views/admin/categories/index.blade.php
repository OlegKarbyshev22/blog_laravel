@extends('admin.layout')
@section('title','Категории')
@section('admin-content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h2 fw-bold mb-0">Категории</h1><a href="{{ route('admin.categories.create') }}" class="btn btn-accent">+ Добавить</a></div>
<div class="card table-card overflow-hidden"><table class="table align-middle mb-0"><thead><tr><th>Название</th><th>Описание</th><th>Публикаций</th><th class="text-end">Действия</th></tr></thead><tbody>
@foreach($categories as $category)<tr><td><strong>{{ $category->name }}</strong><div class="small text-secondary">{{ $category->slug }}</div></td><td>{{ $category->description ?: '—' }}</td><td>{{ $category->posts_count }}</td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.categories.edit',$category) }}">Изменить</a> <form class="d-inline" method="POST" action="{{ route('admin.categories.destroy',$category) }}" onsubmit="return confirm('Удалить категорию?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Удалить</button></form></td></tr>@endforeach
</tbody></table></div>
@endsection
