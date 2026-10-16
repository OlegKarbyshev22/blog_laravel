@if($errors->any())<div class="alert alert-danger"><strong>Исправьте ошибки:</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="card form-card p-4">
    <div class="row g-3">
        <div class="col-md-8"><label class="form-label">Заголовок</label><input class="form-control" name="title" value="{{ old('title',$post->title??'') }}" required></div>
        <div class="col-md-4"><label class="form-label">Категория</label><select class="form-select" name="category_id" required><option value="">Выберите</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id',$post->category_id??'')==$category->id)>{{ $category->name }}</option>@endforeach</select></div>
        <div class="col-12"><label class="form-label">Краткое описание</label><textarea class="form-control" name="excerpt" rows="2" required>{{ old('excerpt',$post->excerpt??'') }}</textarea></div>
        <div class="col-12"><label class="form-label">Текст публикации</label><textarea class="form-control" name="body" rows="12" required>{{ old('body',$post->body??'') }}</textarea></div>
        <div class="col-md-6"><label class="form-label">URL изображения</label><input type="url" class="form-control" name="image_url" value="{{ old('image_url',$post->image_url??'') }}" placeholder="https://..."></div>
        <div class="col-md-3"><label class="form-label">Статус</label><select class="form-select" name="status"><option value="draft" @selected(old('status',$post->status??'draft')==='draft')>Черновик</option><option value="published" @selected(old('status',$post->status??'')==='published')>Опубликовать</option></select></div>
        <div class="col-md-3"><label class="form-label">Дата публикации</label><input type="datetime-local" class="form-control" name="published_at" value="{{ old('published_at',isset($post)&&$post->published_at?$post->published_at->format('Y-m-d\TH:i'):'') }}"></div>
    </div>
    <div class="mt-4 d-flex gap-2"><button class="btn btn-accent">Сохранить</button><a class="btn btn-outline-secondary" href="{{ route('admin.posts.index') }}">Отмена</a></div>
</div>
