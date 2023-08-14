<?php

use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\FeedsController;
use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\API\TripsController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware('auth:sanctum')->group(function() {
    Route::post('verify', [UserController::class, 'verify']);
    Route::post('/feeds/create', [FeedsController::class, 'create']);
    Route::post('/feeds/likes', [FeedsController::class, 'createLike']);
    Route::post('/feeds/saves', [FeedsController::class, 'saveFeed']);
    Route::get('/feeds/likes', [FeedsController::class, 'getAllLikes']);
    Route::get('/feeds/saves', [FeedsController::class, 'getAllSaves']);
    Route::delete('/feeds/delete', [FeedsController::class, 'delete']);
    Route::delete('/feeds/likes/delete/{feed_id}', [FeedsController::class, 'deleteLike']);
    Route::delete('/feeds/saves/delete/{feed_id}', [FeedsController::class, 'deleteSave']);

    Route::post('/trips/create', [TripsController::class, 'create']);
    Route::post('/trips/update', [TripsController::class, 'update']);
    Route::post('/trips/likes', [TripsController::class, 'createLike']);
    Route::post('/trips/saves', [TripsController::class, 'createSave']);
    Route::get('/trips/likes', [TripsController::class, 'getAllLikes']);
    Route::get('/trips/saves', [TripsController::class, 'getAllSaves']);

    Route::delete('/trips/delete', [TripsController::class, 'delete']);
    Route::delete('/trips/likes/delete', [TripsController::class, 'deleteLike']);
    Route::delete('/trips/saves/delete', [TripsController::class, 'deleteSave']);
});


Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/home', [HomeController::class, 'getData']);
Route::get('/users', [UserController::class, 'getAllUsers']);

Route::get('/feeds', [FeedsController::class, 'getAll']);
Route::get('/trips', [TripsController::class, 'getAll']);

