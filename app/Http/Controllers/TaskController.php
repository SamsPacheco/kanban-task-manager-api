<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Column;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{

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

    public function store(Request $request) // POST /api/tasks
    {
        $validated = $request->validate([
            'column_id' => 'required|exists:columns,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
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
                'priority' => $validated['priority'] ?? 'medium',
                'progress_percentage' => $validated['progress_percentage'] ?? 0,
                'due_date' => $validated['due_date'] ?? null,
                'order' => $lastOrder + 1, 
            ]);

            $task->load('column.board');

            return response()->json($task, 201);
        });
    }

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

    public function update(Request $request, string $id) // PUT /api/tasks/{id}
    {
    $validated = $request->validate([
        'title' => 'sometimes|required|string|max:255',
        'description' => 'nullable|string',
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
