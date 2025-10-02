<?php

namespace App\Http\Controllers;

use App\Models\Column;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;


class ColumnController extends Controller
{
    public function index($boardId) // GET /api/boards/{board_id}/columns
    {
        $columns = Column::where('board_id', $boardId)
            ->with('tasks')
            ->orderBy('order')
            ->get();

        return response()->json($columns, 200);
    }

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

        // Evitar que se cambie el nombre de las columnas básicas (opcional)
        $defaultColumns = ['Pendiente', 'En Progreso', 'Finalizado'];
        if (in_array($column->name, $defaultColumns) && isset($validated['name'])) {
            return response()->json(['message' => 'Default columns cannot be renamed'], 403);
        }

        $column->update($validated);

        return response()->json($column);
    }

    public function destroy(string $id) // DELETE /api/columns/{id}
    {
        $column = Column::find($id);

        if (!$column) {
            return response()->json(['message' => 'Column not found'], 404);
        }

        $defaultColumns = ['Pendiente', 'En Progreso', 'Finalizado'];
        if (in_array($column->name, $defaultColumns)) {
            return response()->json(['message' => 'Default columns cannot be deleted'], 403);
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

    // TODO -> reorder columns
    // public function reorder(Request $request)
    // {
    //     $validated = $request->validate([
    //         'board_id' => 'required|exists:boards,id',
    //         'order' => 'required|array',
    //         'order.*' => 'integer|exists:columns,id',
    //     ]);

    //     DB::transaction(function () use ($validated) {
    //         foreach ($validated['order'] as $position => $id) {
    //             Column::where('id', $id)
    //                 ->where('board_id', $validated['board_id'])
    //                 ->update(['order' => $position + 1]);
    //         }
    //     });

    //     return response()->json(['message' => 'Columns reordered successfully']);
    // }

}
