@extends('admin.layout')
@section('title','Новая публикация')
@section('admin-content')<h1 class="h2 fw-bold mb-4">Новая публикация</h1><form method="POST" action="{{ route('admin.posts.store') }}">@csrf @include('admin.posts._form')</form>@endsection
