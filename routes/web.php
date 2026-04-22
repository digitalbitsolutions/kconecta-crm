<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'index'])->name('home');
Route::get('/result', [PageController::class, 'resultAll']);
Route::get('/result/services', [PageController::class, 'resultAllServices']);
Route::get('/result_maps', [PageController::class, 'resultMaps']);
Route::get('/result/{reference}', [PageController::class, 'result']);
Route::get('/result_service/{id}', [PageController::class, 'resultService']);
Route::post('/signup', [PageController::class, 'signup']);
Route::get('/validate_account', [PageController::class, 'validateAccountPage']);
Route::post('/validate_code', [PageController::class, 'validateAccount']);
Route::get('/policy_and_privacy', [PageController::class, 'policyAndPrivacy']);
Route::get('/login/close_session', [PageController::class, 'logout']);

Route::get('/blogs', [BlogController::class, 'showAll']);
Route::get('/blogs/{slug}', [BlogController::class, 'showArticle']);

Route::middleware(['auth', 'provider_or_agent_verified'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('dashboard');

    Route::get('/post/index', [PostController::class, 'index']);
    Route::get('/post/details/{reference}', [PostController::class, 'postDetails']);
    Route::get('/post/create_form/{id}', [PostController::class, 'createForm']);
    Route::get('/post/my_posts', [PostController::class, 'myPosts']);
    Route::post('/post/create', [PostController::class, 'create']);
    Route::post('/post/update', [PostController::class, 'update']);
    Route::get('/post/update_form/{id}', [PostController::class, 'updateForm']);
    Route::get('/post/delete', [PostController::class, 'delete']);
    Route::get('/post/disabledenabled', [PostController::class, 'disabledEnabled']);
    Route::get('/api/delete_more_image', [PostController::class, 'deleteMoreImage']);

    Route::get('/post/services', [PostController::class, 'services']);
    Route::get('/post/services/delete', [PostController::class, 'servicesDelete']);
    Route::get('/post/services/update_form/{id}', [PostController::class, 'servicesUpdate']);
    Route::post('/post/services/update/save', [PostController::class, 'servicesUpdateSave']);
    Route::post('/post/create_service', [PostController::class, 'createService']);

    Route::get('/post/blogs', [BlogController::class, 'index']);
    Route::get('/post/blogs/create', [BlogController::class, 'createBlog']);
    Route::get('/post/blogs/edit/{id}', [BlogController::class, 'edit']);
    Route::post('/post/blogs/article/save', [BlogController::class, 'saveArticle']);
    Route::post('/post/blogs/update/{id}', [BlogController::class, 'update']);
    Route::get('/post/blogs/delete', [BlogController::class, 'delete']);

    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/toggle', [UserController::class, 'toggleStatus']);
    Route::get('/users/edit/{id}', [UserController::class, 'editAdmin']);
    Route::get('/users/{id}', [UserController::class, 'userView']);

    Route::get('/user/update', [UserController::class, 'update']);
    Route::post('/user/update/save', [UserController::class, 'updateSave']);
    Route::get('/user/delete', [UserController::class, 'userDelete']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
