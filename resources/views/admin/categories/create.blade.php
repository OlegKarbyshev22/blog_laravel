@extends('admin.layout')
@section('title','Новая категория')
@section('admin-content')<h1 class="h2 fw-bold mb-4">Новая категория</h1><form method="POST" action="{{ route('admin.categories.store') }}">@csrf @include('admin.categories._form')</form>@endsection
