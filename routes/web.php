<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('platform/index');
});

Route::get('/features', function () {
    return Inertia::render('platform/features');
});

Route::get('/pricing', function () {
    return Inertia::render('platform/pricing');
});

Route::get('/blog', function () {
    return Inertia::render('platform/blog');
});

Route::get('/blog/{slug}', function (string $slug) {
    return Inertia::render('platform/blog/show', ['slug' => $slug]);
});

Route::get('/store', function () {
    return Inertia::render('store/home');
});

// Breeze's auth controllers redirect here after login, registration and email
// verification. There is no merchant dashboard in this build yet, so send the
// user to the storefront until one lands.
Route::get('/dashboard', function () {
    return redirect('/store');
})->middleware('auth')->name('dashboard');

require __DIR__.'/auth.php';