<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $this->authorizeRole(['admin', 'manager', 'teacher']);
        return Course::with('classModel')->get();
    }

    public function show($id)
    {
        $this->authorizeRole(['admin', 'manager', 'teacher']);
        return Course::with('assignments')->findOrFail($id);
    }

    public function store(Request $request)
    {
        $this->authorizeRole(['admin', 'manager', 'teacher']);
        $course = Course::create($request->all());
        return response()->json($course, 201);
    }
}
