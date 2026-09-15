<?php

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::put('page/content', [PageController::class, 'updateContent']);
Route::put('page/theme', [PageController::class, 'updateTheme']);
Route::post('page/apply-template/{template}', [PageController::class, 'applyTemplate']);
