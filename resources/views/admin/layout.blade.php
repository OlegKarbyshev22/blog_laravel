@extends('layouts.app')
@section('content')
<div class="container py-4 admin-shell">
    <div class="admin-nav p-3 mb-4 d-flex flex-wrap align-items-center gap-2">
        <strong class="me-3">Панель администратора</strong>
        <a class="btn btn-sm {{ request()->routeIs('admin.dashboard') ? 'btn-dark' : 'btn-outline-dark' }}" href="{{ route('admin.dashboard') }}">Обзор</a>
        <a class="btn btn-sm {{ request()->routeIs('admin.posts.*') ? 'btn-dark' : 'btn-outline-dark' }}" href="{{ route('admin.posts.index') }}">Публикации</a>
        <a class="btn btn-sm {{ request()->routeIs('admin.categories.*') ? 'btn-dark' : 'btn-outline-dark' }}" href="{{ route('admin.categories.index') }}">Категории</a>
        <a class="btn btn-sm {{ request()->routeIs('admin.comments.*') ? 'btn-dark' : 'btn-outline-dark' }}" href="{{ route('admin.comments.index') }}">Комментарии</a>
        <a class="btn btn-sm {{ request()->routeIs('admin.enrollments.*') ? 'btn-dark' : 'btn-outline-dark' }}" href="{{ route('admin.enrollments.index') }}">Записи на темы</a>
    </div>
    @yield('admin-content')
</div>
@endsection
