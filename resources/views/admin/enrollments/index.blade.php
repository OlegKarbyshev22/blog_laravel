@extends('admin.layout')
@section('title', 'Записи на темы')
@section('admin-content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div><h1 class="h2 fw-bold mb-1">Записи на темы</h1><p class="text-secondary mb-0">На одну тему допускается не более трёх подтверждённых участников.</p></div>
    <form><select class="form-select" name="status" onchange="this.form.submit()"><option value="">Все статусы</option><option value="confirmed" @selected(request('status') === 'confirmed')>Подтверждены</option><option value="cancelled" @selected(request('status') === 'cancelled')>Отменены</option></select></form>
</div>
@error('status')<div class="alert alert-danger">{{ $message }}</div>@enderror
<div class="card table-card overflow-hidden"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Участник</th><th>Тема</th><th>Дата</th><th>Статус</th><th class="text-end">Действия</th></tr></thead>
    <tbody>
    @forelse($enrollments as $enrollment)
        <tr>
            <td><strong>{{ $enrollment->user->name }}</strong><div class="small text-secondary">{{ $enrollment->user->email }}</div></td>
            <td><a href="{{ route('admin.posts.show', $enrollment->post) }}">{{ $enrollment->post->title }}</a></td>
            <td>{{ $enrollment->created_at->format('d.m.Y H:i') }}</td>
            <td><span class="badge text-bg-{{ $enrollment->status === 'confirmed' ? 'success' : 'secondary' }}">{{ $enrollment->status === 'confirmed' ? 'Подтверждена' : 'Отменена' }}</span></td>
            <td class="text-end text-nowrap">
                <form class="d-inline" method="POST" action="{{ route('admin.enrollments.update', $enrollment) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $enrollment->status === 'confirmed' ? 'cancelled' : 'confirmed' }}"><button class="btn btn-sm btn-outline-{{ $enrollment->status === 'confirmed' ? 'warning' : 'success' }}">{{ $enrollment->status === 'confirmed' ? 'Отменить' : 'Подтвердить' }}</button></form>
                <form class="d-inline" method="POST" action="{{ route('admin.enrollments.destroy', $enrollment) }}" onsubmit="return confirm('Удалить запись?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Удалить</button></form>
            </td>
        </tr>
    @empty
        <tr><td colspan="5" class="text-center py-4">Записей пока нет.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-4">{{ $enrollments->links() }}</div>
@endsection
