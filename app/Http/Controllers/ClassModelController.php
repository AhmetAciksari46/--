<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClassModelController extends Controller
{
    public function index()
    {
        $this->authorizeRole(['admin', 'manager']);
        return ClassModel::with('teacher', 'school')->get();
    }

    public function show($id)
    {
        $this->authorizeRole(['admin', 'manager', 'teacher']);
        return ClassModel::with('courses')->findOrFail($id);
    }

    public function store(Request $request)
    {
        $this->authorizeRole(['admin', 'manager']);
        $class = ClassModel::create($request->all());
        return response()->json($class, 201);
    }
}
