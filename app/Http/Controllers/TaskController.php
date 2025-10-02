<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * @OA\Get(
     *     path="/tasks",
     *     tags={"Tasks"},
     *     summary="Obtener todas las tareas",
     *     description="Retorna la lista de todas las tareas con opciones de filtrado por prioridad y progreso. Incluye información de la columna y tablero padre.",
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         description="Filtrar por prioridad de la tarea",
     *         @OA\Schema(type="string", enum={"low", "medium", "high"})
     *     ),
     *     @OA\Parameter(
     *         name="progress",
     *         in="query",
     *         description="Filtrar por porcentaje de progreso (0-100)",
     *         @OA\Schema(type="integer", minimum=0, maximum=100)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tareas obtenidas exitosamente",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="column_id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Diseñar mockups"),
     *                 @OA\Property(property="description", type="string", example="Crear diseños para la nueva feature"),
     *                 @OA\Property(property="assigned_to", type="string", example="Juan Pérez"),
     *                 @OA\Property(property="created_by", type="string", example="Ana García"),
     *                 @OA\Property(property="priority", type="string", example="high", enum={"low", "medium", "high"}),
     *                 @OA\Property(property="progress_percentage", type="integer", example=75),
     *                 @OA\Property(property="due_date", type="string", format="date", example="2024-02-15"),
     *                 @OA\Property(property="order", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(
     *                     property="column",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="En Progreso"),
     *                     @OA\Property(
     *                         property="board",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Proyecto Principal")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request) // GET /api/tasks
    {
        $priority = $request->query('priority');
        $progress = $request->query('progress');

        $query = Task::with('column.board');

        if ($priority) {
            $query->where('priority', $priority);
        }

        if ($progress !== null) {
            $query->where('progress_percentage', $progress);
        }

        $tasks = $query->orderBy('order')->get();

        return response()->json($tasks);
    }

    /**
     * @OA\Post(
     *     path="/tasks",
     *     tags={"Tasks"},
     *     summary="Crear una nueva tarea",
     *     description="Crea una nueva tarea en la columna especificada. El orden se asigna automáticamente al final de la columna.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"column_id", "title"},
     *             @OA\Property(property="column_id", type="integer", example=1, description="ID de la columna donde se creará la tarea"),
     *             @OA\Property(property="title", type="string", example="Implementar login", description="Título de la tarea"),
     *             @OA\Property(property="description", type="string", example="Desarrollar sistema de autenticación", description="Descripción detallada de la tarea"),
     *             @OA\Property(property="assigned_to", type="string", example="María López", description="Persona asignada a la tarea"),
     *             @OA\Property(property="created_by", type="string", example="Carlos Ruiz", description="Persona que creó la tarea"),
     *             @OA\Property(property="priority", type="string", example="high", enum={"low", "medium", "high"}, description="Prioridad de la tarea"),
     *             @OA\Property(property="progress_percentage", type="integer", example=0, minimum=0, maximum=100, description="Porcentaje de progreso (0-100)"),
     *             @OA\Property(property="due_date", type="string", format="date", example="2024-03-01", description="Fecha límite de la tarea")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tarea creada exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="column_id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Implementar login"),
     *             @OA\Property(property="description", type="string", example="Desarrollar sistema de autenticación"),
     *             @OA\Property(property="assigned_to", type="string", example="María López"),
     *             @OA\Property(property="created_by", type="string", example="Carlos Ruiz"),
     *             @OA\Property(property="priority", type="string", example="high"),
     *             @OA\Property(property="progress_percentage", type="integer", example=0),
     *             @OA\Property(property="due_date", type="string", format="date", example="2024-03-01"),
     *             @OA\Property(property="order", type="integer", example=1),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time"),
     *             @OA\Property(
     *                 property="column",
     *                 type="object",
     *                 @OA\Property(
     *                     property="board",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Proyecto Principal")
     *                 )
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
     *     )
     * )
     */
    public function store(Request $request) // POST /api/tasks
    {
        $validated = $request->validate([
            'column_id' => 'required|exists:columns,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|string|max:100',
            'created_by' => 'nullable|string|max:100',
            'priority' => 'nullable|in:low,medium,high',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
            'due_date' => 'nullable|date',
        ]);

        return DB::transaction(function () use ($validated) {
            $lastOrder = Task::where('column_id', $validated['column_id'])
                ->max('order') ?? 0;

            $task = Task::create([
                'column_id' => $validated['column_id'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? '',
                'assigned_to' => $validated['assigned_to'] ?? null,
                'created_by' => $validated['created_by'] ?? 'Usuario',
                'priority' => $validated['priority'] ?? 'medium',
                'progress_percentage' => $validated['progress_percentage'] ?? 0,
                'due_date' => $validated['due_date'] ?? null,
                'order' => $lastOrder + 1,
            ]);

            $task->load('column.board');

            return response()->json($task, 201);
        });
    }

    /**
     * @OA\Get(
     *     path="/tasks/{id}",
     *     tags={"Tasks"},
     *     summary="Obtener una tarea específica",
     *     description="Retorna los detalles completos de una tarea específica, incluyendo información de su columna y tablero padre.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la tarea",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tarea obtenida exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="column_id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Implementar login"),
     *             @OA\Property(property="description", type="string", example="Desarrollar sistema de autenticación"),
     *             @OA\Property(property="assigned_to", type="string", example="María López"),
     *             @OA\Property(property="created_by", type="string", example="Carlos Ruiz"),
     *             @OA\Property(property="priority", type="string", example="high"),
     *             @OA\Property(property="progress_percentage", type="integer", example=75),
     *             @OA\Property(property="due_date", type="string", format="date", example="2024-03-01"),
     *             @OA\Property(property="order", type="integer", example=1),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time"),
     *             @OA\Property(
     *                 property="column",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="En Progreso"),
     *                 @OA\Property(
     *                     property="board",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Proyecto Principal")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tarea no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Task not found")
     *         )
     *     )
     * )
     */
    public function show(string $id) // GET /api/tasks/{id} 
    {
        $task = Task::with('column.board')->find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        }

        return response()->json($task);
    }

    /**
     * @OA\Put(
     *     path="/tasks/{id}",
     *     tags={"Tasks"},
     *     summary="Actualizar una tarea",
     *     description="Actualiza los datos de una tarea existente. Permite cambiar la columna, orden, y todos los demás campos.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la tarea a actualizar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Implementar login con Google", description="Nuevo título de la tarea"),
     *             @OA\Property(property="description", type="string", example="Desarrollar sistema de autenticación con OAuth2", description="Nueva descripción"),
     *             @OA\Property(property="assigned_to", type="string", example="Pedro Martínez", description="Nueva persona asignada"),
     *             @OA\Property(property="priority", type="string", example="medium", enum={"low", "medium", "high"}, description="Nueva prioridad"),
     *             @OA\Property(property="progress_percentage", type="integer", example=90, description="Nuevo porcentaje de progreso"),
     *             @OA\Property(property="due_date", type="string", format="date", example="2024-03-05", description="Nueva fecha límite"),
     *             @OA\Property(property="column_id", type="integer", example=2, description="Nueva columna de la tarea"),
     *             @OA\Property(property="order", type="integer", example=2, description="Nueva posición en la columna")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tarea actualizada exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="column_id", type="integer", example=2),
     *             @OA\Property(property="title", type="string", example="Implementar login con Google"),
     *             @OA\Property(property="description", type="string", example="Desarrollar sistema de autenticación con OAuth2"),
     *             @OA\Property(property="assigned_to", type="string", example="Pedro Martínez"),
     *             @OA\Property(property="priority", type="string", example="medium"),
     *             @OA\Property(property="progress_percentage", type="integer", example=90),
     *             @OA\Property(property="due_date", type="string", format="date", example="2024-03-05"),
     *             @OA\Property(property="order", type="integer", example=2),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tarea no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Task not found")
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
    public function update(Request $request, string $id) // PUT /api/tasks/{id}
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|string|max:100', 
            'created_by' => 'nullable|string|max:100',  
            'priority' => 'sometimes|in:low,medium,high',
            'progress_percentage' => 'sometimes|integer|min:0|max:100',
            'due_date' => 'nullable|date',
            'column_id' => 'sometimes|exists:columns,id',
            'order' => 'sometimes|integer|min:0'
        ]);

        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        }

        $task->update($validated);

        $task->load('column.board');

        return response()->json($task);
    }

    /**
     * @OA\Delete(
     *     path="/tasks/{id}",
     *     tags={"Tasks"},
     *     summary="Eliminar una tarea",
     *     description="Elimina una tarea y reorganiza automáticamente el orden de las tareas restantes en la misma columna.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la tarea a eliminar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tarea eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Task deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tarea no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Task not found")
     *         )
     *     )
     * )
     */
    public function destroy(string $id) // DELETE /api/tasks/{id}
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        }

        return DB::transaction(function () use ($task) {
            $columnId = $task->column_id;
            $deletedOrder = $task->order;

            $task->delete();

            Task::where('column_id', $columnId)
                ->where('order', '>', $deletedOrder)
                ->decrement('order');

            return response()->json([
                'message' => 'Task deleted successfully'
            ], 200);
        });
    }
}