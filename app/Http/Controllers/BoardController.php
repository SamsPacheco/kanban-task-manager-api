<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Column;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Info(
 *     title="Kanban API", 
 *     version="1.0.0"
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Servidor Local"
 * )
 */
class BoardController extends Controller
{

    /**
     * @OA\Get(
     *     path="/boards",
     *     tags={"Boards"},
     *     summary="Obtener todos los tableros",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de tableros obtenida exitosamente"
     *     )
     * )
     */
    public function index() // GET /api/boards
    {
        $boards = Board::all();
        return response()->json($boards);
    }
    /**
     * @OA\Post(
     *     path="/boards",
     *     tags={"Boards"},
     *     summary="Crear un nuevo tablero",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Mi Proyecto"),
     *             @OA\Property(property="description", type="string", example="Descripción del proyecto")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tablero creado exitosamente"
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/boards/{id}",
     *     tags={"Boards"},
     *     summary="Obtener un tablero específico",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del tablero",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tablero obtenido exitosamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tablero no encontrado"
     *     )
     * )
     */
    public function show(string $id) //GET /api/boards/{id} - Obtener tablero con columnas y tareas
    {
        $board = Board::with('columns.tasks')->find($id);

        if (!$board) {
            return response()->json(['message' => 'Tablero no encontrado'], 404);
        }

        return response()->json($board);
    }

    /**
     * @OA\Put(
     *     path="/boards/{id}",
     *     tags={"Boards"},
     *     summary="Actualizar un tablero",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del tablero",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Nombre Actualizado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tablero actualizado exitosamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tablero no encontrado"
     *     )
     * )
     */

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

    /**
     * @OA\Delete(
     *     path="/boards/{id}",
     *     tags={"Boards"},
     *     summary="Eliminar un tablero",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del tablero",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tablero eliminado exitosamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tablero no encontrado"
     *     )
     * )
     */

    public function destroy(string $id)      // DELETE /api/boards/{id}
    {
        $board = Board::find($id);

        if (!$board) {
            return response()->json(['message' => 'Tablero no encontrado'], 404);
        }

        $board->delete();
        return response()->json(['message' => 'Tablero eliminado correctamente']);
    }

    /**
     * @OA\Get(
     *     path="/boards/{id}/tasks",
     *     tags={"Boards"},
     *     summary="Obtener tareas de un tablero con filtros",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del tablero",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="prioridad",
     *         in="query",
     *         description="Filtrar por prioridad",
     *         @OA\Schema(type="string", enum={"alta", "media", "baja"})
     *     ),
     *     @OA\Parameter(
     *         name="porcentaje",
     *         in="query",
     *         description="Filtrar por porcentaje de progreso",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="vencimiento",
     *         in="query",
     *         description="Filtrar por fecha de vencimiento (YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="buscar",
     *         in="query",
     *         description="Buscar en título y descripción",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tareas obtenidas exitosamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tablero no encontrado"
     *     )
     * )
     */

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

        return response()->json($tasks->values());
    }
}
