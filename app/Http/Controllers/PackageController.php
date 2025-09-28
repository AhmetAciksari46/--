<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PackageController extends Controller
{
   public function index()
    {
        $this->authorizeRole(['admin']);
        return Package::all();
    }

    public function show($id)
    {
        $this->authorizeRole(['admin']);
        return Package::with('subscriptions')->findOrFail($id);
    }

    public function store(Request $request)
    {
        $this->authorizeRole(['admin']);
        $package = Package::create($request->all());
        return response()->json($package, 201);
    }
}
