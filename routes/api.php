<?php


use App\Http\Controllers\BoardController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::apiResource('boards', BoardController::class);
Route::apiResource('columns', ColumnController::class); 
Route::apiResource('tasks', TaskController::class);

Route::get('/boards/{board}/columns', [ColumnController::class, 'index']);
Route::post('/boards/{board}/columns', [ColumnController::class, 'store']);

// Endpoints extra para Kanban
// Route::put('columns/reorder', [ColumnController::class, 'reorder']);
// Route::put('tasks/reorder', [TaskController::class, 'reorder']);
// Route::put('tasks/{id}/move', [TaskController::class, 'move']);


Route::get('/test', function () {
    return response()->json(['message' => 'API funciona!']);
});

//Rutas personalizadas
// GET /tableros/:id/tareas - Con filtros

