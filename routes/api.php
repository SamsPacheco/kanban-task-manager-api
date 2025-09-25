<?php


use App\Http\Controllers\BoardController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::apiResource('boards', BoardController::class);
Route::apiResource('columns', ColumnController::class); 
Route::apiResource('tasks', TaskController::class);

Route::get('/test', function () {
    return response()->json(['message' => 'API funciona!']);
});