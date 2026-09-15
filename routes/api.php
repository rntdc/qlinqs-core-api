<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\TemplateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('page', [PageController::class, 'show']);
Route::put('page/content', [PageController::class, 'updateContent']);
Route::put('page/theme', [PageController::class, 'updateTheme']);
Route::post('page/apply-template/{template}', [PageController::class, 'applyTemplate']);

Route::get('templates', [TemplateController::class, 'index']);
