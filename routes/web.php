<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\Account\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/auth/{provider}', [AuthController::class, 'auth']);
Route::get('/auth/{provider}/callback', [AuthController::class, 'callback']);
