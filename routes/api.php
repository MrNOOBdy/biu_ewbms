<?php

use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\PreloadDataController;
use App\Http\Controllers\Api\v1\SyncController;
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

Route::post('/login', [AuthController::class, 'login']);
Route::get('/connect', [AuthController::class, 'connect']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/get_coverage_date', [PreloadDataController::class, 'get_coverage_date'])->middleware('auth:sanctum');
Route::get('/get_consumer', [PreloadDataController::class, 'get_consumer'])->middleware('auth:sanctum');
Route::get('/get_bill_rate', [PreloadDataController::class, 'get_bill_rate'])->middleware('auth:sanctum');

Route::post('/sync', [SyncController::class, 'sync'])->middleware('auth:sanctum');
