<?php


use App\Http\Controllers\BoardController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::apiResource('boards', BoardController::class);
Route::apiResource('tasks', TaskController::class);

Route::get('/boards/{board}/columns', [ColumnController::class, 'index']);
Route::post('/boards/{board}/columns', [ColumnController::class, 'store']);

Route::get('/boards/{board}/columns', [ColumnController::class, 'index']);
Route::post('/boards/{board}/columns', [ColumnController::class, 'store']);
Route::get('/columns/{column}', [ColumnController::class, 'show']);
Route::put('/columns/{column}', [ColumnController::class, 'update']);
Route::delete('/columns/{column}', [ColumnController::class, 'destroy']);





Route::get('/test', function () {
    return response()->json(['message' => 'API funciona!']);
});



