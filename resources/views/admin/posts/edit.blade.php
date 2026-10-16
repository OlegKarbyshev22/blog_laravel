@extends('admin.layout')
@section('title','Редактирование')
@section('admin-content')<h1 class="h2 fw-bold mb-4">Редактирование публикации</h1><form method="POST" action="{{ route('admin.posts.update',$post) }}">@csrf @method('PUT') @include('admin.posts._form')</form>@endsection
