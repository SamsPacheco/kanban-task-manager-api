<?php

namespace App\Http\Controllers;

use App\Models\Column;
use Illuminate\Http\Request;

class ColumnController extends Controller
{
    /**
     * @OA\Get(
     *     path="/boards/{board}/columns",
     *     tags={"Columns"},
     *     summary="Obtener todas las columnas de un tablero",
     *     description="Retorna la lista de todas las columnas de un tablero específico, incluyendo sus tareas ordenadas",
     *     @OA\Parameter(
     *         name="board",
     *         in="path",
     *         required=true,
     *         description="ID del tablero",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Columnas obtenidas exitosamente",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="board_id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Pendiente"),
     *                 @OA\Property(property="order", type="integer", example=1),
     *                 @OA\Property(property="color", type="string", example="#ba181b"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(
     *                     property="tasks",
     *                     type="array",
     *                     @OA\Items(type="object")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index($boardId) // GET /api/boards/{board_id}/columns
    {
        $columns = Column::where('board_id', $boardId)
            ->with('tasks')
            ->orderBy('order')
            ->get();

        return response()->json($columns, 200);
    }

    /**
     * @OA\Post(
     *     path="/boards/{board}/columns",
     *     tags={"Columns"},
     *     summary="Crear una nueva columna en un tablero",
     *     description="Crea una nueva columna en el tablero especificado. Si no se proporciona el orden, se asigna automáticamente al final.",
     *     @OA\Parameter(
     *         name="board",
     *         in="path",
     *         required=true,
     *         description="ID del tablero donde se creará la columna",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="En Revisión", description="Nombre de la columna"),
     *             @OA\Property(property="color", type="string", example="#ffa500", description="Color de la columna en formato HEX"),
     *             @OA\Property(property="order", type="integer", example=4, description="Posición de la columna en el tablero")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Columna creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Column created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=4),
     *                 @OA\Property(property="board_id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="En Revisión"),
     *                 @OA\Property(property="color", type="string", example="#ffa500"),
     *                 @OA\Property(property="order", type="integer", example=4),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to create column"),
     *             @OA\Property(property="error", type="string", example="Error message details")
     *         )
     *     )
     * )
     */
    public function store(Request $request, $boardId) // POST /api/boards/{board_id}/columns
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:20',
            'order' => 'nullable|integer',
        ]);

        try {
            // Obtiene el último orden de las columnas en el tablero
            $lastOrder = Column::where('board_id', $boardId)->max('order') ?? 0;

            $column = Column::create([
                'board_id' => $boardId,
                'name' => $validated['name'],
                'color' => $validated['color'] ?? '#cccccc',
                'order' => $validated['order'] ?? $lastOrder + 1,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Column created successfully',
                'data' => $column
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create column',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/columns/{id}",
     *     tags={"Columns"},
     *     summary="Obtener una columna específica",
     *     description="Retorna los detalles de una columna específica incluyendo todas sus tareas",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la columna",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Columna obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Column retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="board_id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Pendiente"),
     *                 @OA\Property(property="order", type="integer", example=1),
     *                 @OA\Property(property="color", type="string", example="#ba181b"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(
     *                     property="tasks",
     *                     type="array",
     *                     @OA\Items(type="object")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Columna no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Column not found")
     *         )
     *     )
     * )
     */
    public function show(string $id) // GET /api/columns/{id}
    {
        $column = Column::with('tasks')->find($id);

        if (!$column) {
            return response()->json([
                'status' => 'error',
                'message' => 'Column not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Column retrieved successfully',
            'data' => $column
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/columns/{id}",
     *     tags={"Columns"},
     *     summary="Actualizar una columna",
     *     description="Actualiza los datos de una columna existente. Las columnas predeterminadas pueden ser editadas.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la columna a actualizar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Por Hacer", description="Nuevo nombre de la columna"),
     *             @OA\Property(property="color", type="string", example="#ff5733", description="Nuevo color de la columna")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Columna actualizada exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="board_id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Por Hacer"),
     *             @OA\Property(property="order", type="integer", example=1),
     *             @OA\Property(property="color", type="string", example="#ff5733"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Columna no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Column not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(Request $request, string $id) // PUT /api/columns/{id}
    {
        $column = Column::find($id);

        if (!$column) {
            return response()->json(['message' => 'Column not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'color' => 'nullable|string|max:20',
        ]);

        $column->update($validated);

        return response()->json($column);
    }

    /**
     * @OA\Delete(
     *     path="/columns/{id}",
     *     tags={"Columns"},
     *     summary="Eliminar una columna",
     *     description="Elimina una columna y todas sus tareas. Reorganiza automáticamente el orden de las columnas restantes.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la columna a eliminar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Columna eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Column deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Columna no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Column not found")
     *         )
     *     )
     * )
     */
    public function destroy(string $id) // DELETE /api/columns/{id}
    {
        $column = Column::find($id);

        if (!$column) {
            return response()->json(['message' => 'Column not found'], 404);
        }

        $deletedOrder = $column->order;
        $boardId = $column->board_id;

        $column->tasks()->delete();
        $column->delete();

        Column::where('board_id', $boardId)
            ->where('order', '>', $deletedOrder)
            ->decrement('order');

        return response()->json(['message' => 'Column deleted successfully']);
    }
}