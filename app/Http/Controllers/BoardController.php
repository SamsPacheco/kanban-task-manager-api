<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Column;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoardController extends Controller
{
    public function index() // GET /api/boards
    {
        $boards = Board::all();
        return response()->json($boards);
    }

    // public function store(Request $request) // POST /api/boards
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //     ]);

    //     $board = Board::create($request->all());
    //     return response()->json($board, 201); // 201 Created
    // }

    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
    ]);

    return DB::transaction(function () use ($validated) {
        // 1. Crear el tablero
        $board = Board::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
        ]);

        // 2. Crear las 3 columnas iniciales
        $defaultColumns = [
            ['name' => 'Pendiente', 'order' => 1, 'color' => '#ba181b'],
            ['name' => 'En Progreso', 'order' => 2, 'color' => '#22577a'],
            ['name' => 'Finalizado', 'order' => 3, 'color' => '#80ed99'],
        ];

        foreach ($defaultColumns as $col) {
            Column::create([
                'board_id' => $board->id,
                'name' => $col['name'],
                'order' => $col['order'],
                'color' => $col['color'],
            ]);
        }

        // 3. Cargar las columnas en el tablero
        $board->load('columns');

        return response()->json([
            'status' => 'success',
            'message' => 'Board created successfully with default columns',
            'data' => $board
        ], 201);
    });
}


    public function show(string $id) //GET /api/boards/{id} - Obtener tablero con columnas y tareas
    {
        $board = Board::with('columns.tasks')->find($id);

        if (!$board) {
            return response()->json(['message' => 'Tablero no encontrado'], 404);
        }

        return response()->json($board);
    }

    public function update(Request $request, string $id) // PUT /api/boards/{id}
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $board = Board::find($id);

        if (!$board) {
            return response()->json(['message' => 'Tablero no encontrado'], 404);
        }

        $board->update($request->all());
        return response()->json($board);
    }

    public function destroy(string $id)      // DELETE /api/boards/{id}
    {
        $board = Board::find($id);

        if (!$board) {
            return response()->json(['message' => 'Tablero no encontrado'], 404);
        }

        $board->delete();
        return response()->json(['message' => 'Tablero eliminado correctamente']);
    }

    // Nuevo endpoint: GET /tableros/:id/tareas - Con filtros
    public function boardTasks(Request $request, string $id)
    {
        $board = Board::find($id);

        if (!$board) {
            return response()->json(['message' => 'Tablero no encontrado'], 404);
        }

        $tasks = collect();
        foreach ($board->columns as $column) {
            $tasks = $tasks->concat($column->tasks);
        }

        // Aplicar filtros
        if ($request->has('prioridad')) {
            $tasks = $tasks->where('priority', $request->prioridad);
        }
        if ($request->has('porcentaje')) {
            $tasks = $tasks->where('progress_percentage', $request->porcentaje);
        }
        if ($request->has('vencimiento')) {
            // Asumiendo que el formato de fecha es 'YYYY-MM-DD'
            $tasks = $tasks->where('due_date', $request->vencimiento);
        }
        if ($request->has('buscar')) {
            $searchTerm = strtolower($request->buscar);
            $tasks = $tasks->filter(function ($task) use ($searchTerm) {
                return str_contains(strtolower($task->title), $searchTerm) ||
                       str_contains(strtolower($task->description), $searchTerm);
            });
        }

        return response()->json($tasks->values()); // values() para reindexar el array
    }
}