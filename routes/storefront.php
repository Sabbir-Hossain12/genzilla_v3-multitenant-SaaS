<?php

use App\Http\Controllers\Store\StoreWebviewController;

Route::middleware(['web','tenant.identify','store.status'])->group(function () {
    Route::get('/', [StoreWebviewController::class, 'index']);
});
