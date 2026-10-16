<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EnrollmentController as AdminEnrollmentController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RatingController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');

Auth::routes();
Route::get('/home', fn () => redirect()->route('posts.index'))->name('home');

Route::middleware('auth')->group(function () {
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::post('/posts/{post}/rating', [RatingController::class, 'store'])->name('ratings.store');
    Route::post('/posts/{post}/enrollment', [EnrollmentController::class, 'store'])->name('enrollments.store');
    Route::delete('/posts/{post}/enrollment', [EnrollmentController::class, 'destroy'])->name('enrollments.destroy');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('posts', AdminPostController::class);
    Route::resource('categories', AdminCategoryController::class)->except('show');
    Route::get('comments', [AdminCommentController::class, 'index'])->name('comments.index');
    Route::patch('comments/{comment}', [AdminCommentController::class, 'update'])->name('comments.update');
    Route::delete('comments/{comment}', [AdminCommentController::class, 'destroy'])->name('comments.destroy');
    Route::get('enrollments', [AdminEnrollmentController::class, 'index'])->name('enrollments.index');
    Route::patch('enrollments/{enrollment}', [AdminEnrollmentController::class, 'update'])->name('enrollments.update');
    Route::delete('enrollments/{enrollment}', [AdminEnrollmentController::class, 'destroy'])->name('enrollments.destroy');
});
