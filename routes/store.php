<?php

use App\Http\Controllers\Store\StoreWebviewController;

Route::middleware( 'identify.storefront')->group(function () {
    Route::get('/', [StoreWebviewController::class, 'index']);
});
