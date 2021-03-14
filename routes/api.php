<?php

use App\Http\Controllers\Api\EpisodeController;
use App\Http\Controllers\Api\FlagController;
use App\Http\Controllers\Api\HostController;
use App\Http\Controllers\Api\SubtopicController;
use App\Http\Controllers\Api\TopicController;
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

// episodes
Route::apiResource('episodes', EpisodeController::class);
Route::get('episodes/{episode}/hosts', [HostController::class, 'indexScoped'])->name('episodes.hosts.index');
Route::post('episodes/{episode}/vote', [EpisodeController::class, 'vote'])->name('episodes.vote');

// episode claims
Route::post('episodes/{episode}/claim', [EpisodeController::class, 'claim'])->name('episodes.claim.store');
Route::delete('episodes/{episode}/claim', [EpisodeController::class, 'unclaim'])->name('episodes.claim.destroy');

// flags
Route::get('flags', [FlagController::class, 'index'])->name('flags.index');
Route::get('episodes/{episode}/flags', [FlagController::class, 'indexScoped'])->name('episodes.flags.index');
Route::apiResource('episodes.flags', FlagController::class)->except(['index'])->shallow();

// hosts
Route::post('hosts/{host}/episodes/{episode}', [HostController::class, 'attachEpisode'])->name('hosts.episodes.attach');
Route::delete('hosts/{host}/episodes/{episode}', [HostController::class, 'detachEpisode'])->name('hosts.episodes.detach');
Route::apiResource('hosts', HostController::class);

// topics
Route::apiResource('episodes.topics', TopicController::class)->except(['index'])->shallow();
Route::get('topics', [TopicController::class, 'index'])->name('topics.index');
Route::get('episodes/{episode}/topics', [TopicController::class, 'indexScoped'])->name('episodes.topics.index');

// subtopics
Route::apiResource('topics.subtopics', SubtopicController::class)->except(['index'])->shallow();
Route::get('topics/{topic}/subtopics', [SubtopicController::class, 'indexScoped'])->name('topics.subtopics.index');
Route::get('subtopics', [SubtopicController::class, 'index'])->name('subtopics.index');
