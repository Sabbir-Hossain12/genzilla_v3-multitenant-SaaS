<?php

use App\Http\Controllers\Platform\BlogController;
use App\Http\Controllers\Platform\NewsletterController;
use App\Http\Controllers\Platform\PlatformWebviewController;
use Illuminate\Support\Facades\Route;

Route::domain(config('app.base_domain'))->group(function () {
    Route::get('/', [PlatformWebviewController::class, 'index']);
    Route::get('/features', [PlatformWebviewController::class, 'features']);
    Route::get('/pricing', [PlatformWebviewController::class, 'pricing']);
    Route::get('/blog', [BlogController::class, 'index']);
    Route::get('/blog/{slug}', [BlogController::class, 'show']);
    Route::post('/newsletter', [NewsletterController::class, 'store'])->name('platform.newsletter');
});

// Breeze's auth controllers redirect here after login, registration and email
// verification. There is no merchant dashboard in this build yet, so send the
// user to the storefront until one lands.
Route::get('/dashboard', function () {
    return redirect('/store');
})->middleware('auth')->name('dashboard');
