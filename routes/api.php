<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MeditationController;


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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/meditations', [MeditationController::class, 'index']);
Route::get('/meditations/{meditation}', [MeditationController::class, 'show']);
Route::post('/meditations', [MeditationController::class, 'store']);
Route::post('/meditations/{meditation}', [MeditationController::class, 'update']);
Route::delete('/meditations/{meditation}', [MeditationController::class, 'destroy']);
