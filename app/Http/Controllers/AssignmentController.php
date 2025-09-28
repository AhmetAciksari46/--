<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AssignmentController extends Controller
{
        public function index()
    {
        $this->authorizeRole(['admin', 'manager', 'teacher']);
        return Assignment::with('course')->get();
    }

    public function show($id)
    {
        $this->authorizeRole(['admin', 'manager', 'teacher', 'schoolstudent']);
        return Assignment::findOrFail($id);
    }

    public function store(Request $request)
    {
        $this->authorizeRole(['admin', 'manager', 'teacher']);
        $assignment = Assignment::create($request->all());
        return response()->json($assignment, 201);
    }
}
