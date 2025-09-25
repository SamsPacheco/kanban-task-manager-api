<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TaskController extends Controller
{

    public function index() // GET /api/tasks
    {
        return response()->json(['message' => 'GET Tasks - FUNCIONA']);
    }

    public function store(Request $request) // POST /api/tasks
    {
        return response()->json(['message' => 'POST Task - FUNCIONA']);
    }

    public function show(string $id) // GET /api/tasks/{id} 
    {
        return response()->json(['message' => "GET Task $id - FUNCIONA"]);
    }

    public function update(Request $request, string $id) // PUT /api/tasks/{id}
    {
        return response()->json(['message' => "PUT Task $id - FUNCIONA"]);
    }

    public function destroy(string $id) // DELETE /api/tasks/{id}
    {
        return response()->json(['message' => "DELETE Task $id - FUNCIONA"]);
    }
}
