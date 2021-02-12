<?php

use App\Http\Controllers\Api\EpisodeController;
use App\Http\Controllers\Api\SubtopicController;
use App\Http\Controllers\Api\TopicController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::apiResource('users', UserController::class);

Route::apiResource('episodes', EpisodeController::class);

Route::get('topics', [TopicController::class, 'index'])->name('topics.index');
Route::apiResource('episodes.topics', TopicController::class)->shallow();

Route::get('subtopics', [SubtopicController::class, 'index'])->name('subtopics.index');
Route::apiResource('topics.subtopics', SubtopicController::class)->shallow();
