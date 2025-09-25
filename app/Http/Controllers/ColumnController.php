<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ColumnController extends Controller
{
    public function index() // GET /api/columns
    {
        return response()->json(['message' => 'GET Columns - FUNCIONA']);
    }

    public function store(Request $request) // POST /api/columns
    {
        return response()->json(['message' => 'POST Column - FUNCIONA']);
    }

    public function show(string $id) // GET /api/columns/{id}
    {
        return response()->json(['message' => "GET Column $id - FUNCIONA"]);
    }

    public function update(Request $request, string $id) // PUT /api/columns/{id}
    {
        return response()->json(['message' => "PUT Column $id - FUNCIONA"]);
    }

    public function destroy(string $id) // DELETE /api/columns/{id}
    {
        return response()->json(['message' => "DELETE Column $id - FUNCIONA"]);
    }
}
