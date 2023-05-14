<?php

namespace App\Http\Controllers;


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




Route::post('register', [AuthController::class,'register']);
Route::post('login', [AuthController::class,'login']);

Route::group(['middleware' => 'auth:api'], function () {
    Route::get('agents', [AgentController::class, 'index']);
    Route::get('agents/{userId}', [AgentController::class, 'show']);
    Route::patch('agents/{id}/update', [AdminController::class, 'updateAgent']);
    Route::delete('agents/{id}/delete', [AdminController::class, 'deleteAgent']);
    Route::post('new-collection/{userId}', [CollectionController::class, 'collecte']);
    Route::get('location/{collectionId}', [CollectionController::class, 'getLastLocation']);
    Route::get('location/{collectionId}/all', [CollectionController::class, 'getAllLocations']);
    Route::post('new-location/{collectionId}', [LocationController::class, 'addLocation']);
    Route::post('logout', [AuthController::class,'logout']);
});
