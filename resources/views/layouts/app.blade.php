<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Точка зрения') — блог</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('posts.index') }}"><span>●</span> Точка зрения</a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="{{ route('posts.index') }}">Публикации</a></li>
                @auth
                    @if(auth()->user()->isAdmin())
                        <li class="nav-item"><a class="btn btn-sm btn-outline-light" href="{{ route('admin.dashboard') }}">Управление</a></li>
                    @endif
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">{{ auth()->user()->name }}</a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <form action="{{ route('logout') }}" method="POST">@csrf<button class="dropdown-item">Выйти</button></form>
                        </div>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Войти</a></li>
                    <li class="nav-item"><a class="btn btn-sm btn-accent" href="{{ route('register') }}">Регистрация</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

@if(session('success') || session('error'))
    <div class="container mt-3"><div class="alert alert-{{ session('error') ? 'danger' : 'success' }} mb-0">{{ session('error') ?? session('success') }}</div></div>
@endif

<main>@yield('content')</main>

<footer class="py-5 mt-5 border-top">
    <div class="container d-flex flex-wrap justify-content-between gap-2 text-secondary">
        <span>© {{ date('Y') }} «Точка зрения»</span><span>Учебный проект на Laravel {{ app()->version() }}</span>
    </div>
</footer>
</body>
</html>
