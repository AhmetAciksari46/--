<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ApiResponser; // <<< Bu satırı ekleyin!

class UserController extends Controller
{
            use ApiResponser; // <<< Trait'i kullanıma alın!

    public function index() // tüm kullanıcılar
    {
        $this->authorizeRole(['admin', 'manager']);
        return User::with([
            'individualStudentProfile',
            'schoolStudentProfile',
            'teacherProfile',
            'managerProfile'
        ])->get();
    }

    public function show($id) // tek kullanıcı
    {
        $this->authorizeRole(['admin', 'manager', 'teacher']);
        $user =  User::with([
            'individualStudentProfile',
            'schoolStudentProfile',
            'teacherProfile',
            'managerProfile'
        ])->findOrFail($id);
         if (!$user) {
            return $this->errorResponse('user_not_found', 404);
        }
        return $this->successResponse($user, 'Kullanıcı bilgileri başarıyla alındı.');

    }

    public function store(Request $request) // yeni kullanıcı
    {
        $this->authorizeRole(['admin', 'manager']);
        $user = User::create($request->all());
        return response()->json($user, 201);
    }

    public function update(Request $request, $id)
    {
        $this->authorizeRole(['admin', 'manager']);
        $user = User::findOrFail($id);
        $user->update($request->all());
        return response()->json($user);
    }

    public function destroy($id)
    {
        $this->authorizeRole(['admin']);
        User::destroy($id);
        return response()->json(null, 204);
    }
}
