<?php

use App\Http\Controllers\AuthorController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::apiResource('documents', DocumentController::class, [
    'except' => ['store'],
]);

Route::apiResources([
    'tags' => TagController::class,
    'authors' => AuthorController::class,
]);

// It's essential for web-based frontends
// Slugs are very important for SEO
Route::get('documents/by-slug/{document:slug}', [DocumentController::class, 'show']);
Route::get('tags/by-slug/{tag:slug}', [TagController::class, 'show']);
Route::get('authors/by-slug/{author:slug}', [AuthorController::class, 'show']);
