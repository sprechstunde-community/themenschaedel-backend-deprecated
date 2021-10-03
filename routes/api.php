<?php

use App\Http\Controllers\Api\EpisodeController;
use App\Http\Controllers\Api\FlagController;
use App\Http\Controllers\Api\HostController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\SubtopicController;
use App\Http\Controllers\Api\TopicController;
use App\Http\Controllers\Api\AuthController;
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

// authentication
Route::post('auth/register', [AuthController::class, 'register'])->name('user.register');
Route::post('auth/login', [AuthController::class, 'login'])->name('user.login');
Route::middleware('auth:sanctum')->group(function () {
    Route::delete('auth/logout', [AuthController::class, 'logout'])->name('user.logout');
    Route::delete('auth/logout/all', [AuthController::class, 'reset'])->name('user.logout.everywhere');
});

// search
Route::get('search/episodes', [SearchController::class, 'episodes'])->name('search.episodes');
Route::get('search/topics', [SearchController::class, 'topics'])->name('search.topics');
Route::get('search/subtopics', [SearchController::class, 'subtopics'])->name('search.subtopics');

// episodes
Route::apiResource('episodes', EpisodeController::class)->only(['index', 'show']);
Route::get('episodes/{episode}/hosts', [HostController::class, 'indexScoped'])->name('episodes.hosts.index');

// flags
Route::apiResource('flags', FlagController::class)->only(['index', 'show']);
Route::get('episodes/{episode}/flags', [FlagController::class, 'indexScoped'])->name('episodes.flags.index');

// hosts
Route::apiResource('hosts', HostController::class)->only(['index', 'show']);

// topics
Route::apiResource('topics', TopicController::class)->only(['index', 'show']);
Route::get('episodes/{episode}/topics', [TopicController::class, 'indexScoped'])->name('episodes.topics.index');

// subtopics
Route::apiResource('subtopics', SubtopicController::class)->only(['index', 'show']);
Route::get('topics/{topic}/subtopics', [SubtopicController::class, 'indexScoped'])->name('topics.subtopics.index');

// restricted API endpoints, that require user to be authenticated
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('episodes/{episode}/claim', [EpisodeController::class, 'claim'])->name('episodes.claim.store');
    Route::delete('episodes/{episode}/claim', [EpisodeController::class, 'unclaim'])->name('episodes.claim.destroy');

    Route::post('episodes/{episode}/vote', [EpisodeController::class, 'vote'])->name('episodes.vote');

    Route::post('hosts/{host}/episodes/{episode}', [HostController::class, 'attachEpisode'])->name('hosts.episodes.attach');
    Route::delete('hosts/{host}/episodes/{episode}', [HostController::class, 'detachEpisode'])->name('hosts.episodes.detach');

    Route::apiResource('episodes', EpisodeController::class)->except(['index', 'show']);
    Route::apiResource('hosts', HostController::class)->except(['index', 'show']);
    Route::apiResource('episodes.flags', FlagController::class)->except(['index', 'show'])->shallow();
    Route::apiResource('episodes.topics', TopicController::class)->except(['index', 'show'])->shallow();
    Route::apiResource('topics.subtopics', SubtopicController::class)->except(['index', 'show'])->shallow();
});
