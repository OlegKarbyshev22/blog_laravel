@extends('admin.layout')
@section('title',$post->title)
@section('admin-content')<div class="card form-card p-4"><div class="d-flex justify-content-between"><span class="badge text-bg-{{ $post->status==='published'?'success':'secondary' }} align-self-start">{{ $post->status }}</span><a href="{{ route('admin.posts.edit',$post) }}" class="btn btn-outline-primary">Изменить</a></div><h1 class="mt-3">{{ $post->title }}</h1><p class="lead">{{ $post->excerpt }}</p><hr><div style="white-space:pre-line">{{ $post->body }}</div></div>@endsection
