<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.categories.index', ['categories' => Category::withCount('posts')->orderBy('name')->get()]);
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['name']);
        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Категория создана.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validated($request, $category);
        $data['slug'] = Str::slug($data['name']);
        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Категория обновлена.');
    }

    public function destroy(Category $category)
    {
        if ($category->posts()->exists()) {
            return back()->with('error', 'Нельзя удалить категорию с публикациями.');
        }
        $category->delete();

        return back()->with('success', 'Категория удалена.');
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories')->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
