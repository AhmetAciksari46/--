<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index()
    {
        $this->authorizeRole(['admin']);
        return School::with('classes', 'manager')->get();
    }

    public function show($id)
    {
        $this->authorizeRole(['admin', 'manager']);
        return School::with('classes')->findOrFail($id);
    }

    public function store(Request $request)
    {
        $this->authorizeRole(['admin']);
        $school = School::create($request->all());
        return response()->json($school, 201);
    }
}
