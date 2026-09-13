<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response('ok', 200)->header('Content-Type', 'text/plain'));
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');
