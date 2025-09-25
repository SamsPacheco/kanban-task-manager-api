<?php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\Http\Request;

class BoardController extends Controller
{
     public function index() // GET /api/boards
    {
        return response()->json(['message' => 'GET Boards - FUNCIONA']);
    }

    public function store(Request $request) // POST /api/boards
    {
        return response()->json(['message' => 'POST Board - FUNCIONA']);
    }

    public function show(string $id) //GET /api/boards/{id}
    {
        return response()->json(['message' => "GET Board $id - FUNCIONA"]);
    }

    public function update(Request $request, string $id) // PUT /api/boards/{id}
    {
        return response()->json(['message' => "PUT Board $id - FUNCIONA"]);
    }

    public function destroy(string $id)     //public function destroy()  // DELETE /api/boards/{id}
    {
        return response()->json(['message' => "DELETE Board $id - FUNCIONA"]);
    }
}