@extends('admin.layout')
@section('title','Изменить категорию')
@section('admin-content')<h1 class="h2 fw-bold mb-4">Изменить категорию</h1><form method="POST" action="{{ route('admin.categories.update',$category) }}">@csrf @method('PUT') @include('admin.categories._form')</form>@endsection
