<?php

use App\Http\Controllers\Api\EpisodeClaimController;
use App\Http\Controllers\Api\EpisodeController;
use App\Http\Controllers\Api\FlagController;
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
Route::apiResource('episodes.topics', TopicController::class)->except(['index'])->shallow();
Route::get('episodes/{episode}/topics', [TopicController::class, 'indexScoped'])->name('episodes.topics.index');
Route::post('episodes/{episode}/claim', [EpisodeClaimController::class, 'store'])->name('episodes.claim.store');
Route::delete('episodes/{episode}/claim', [EpisodeClaimController::class, 'destroy'])->name('episodes.claim.destroy');
Route::post('episodes/{episode}/vote', [EpisodeController::class, 'vote'])->name('episodes.vote');

Route::get('flags', [FlagController::class, 'index'])->name('flags.index');
Route::get('episodes/{episode}/flags', [FlagController::class, 'indexScoped'])->name('episodes.flags.index');
Route::apiResource('episodes.flags', FlagController::class)->except(['index'])->shallow();

Route::get('subtopics', [SubtopicController::class, 'index'])->name('subtopics.index');
Route::get('topics/{topic}/subtopics', [SubtopicController::class, 'indexScoped'])->name('topics.subtopics.index');
Route::apiResource('topics.subtopics', SubtopicController::class)->except(['index'])->shallow();
