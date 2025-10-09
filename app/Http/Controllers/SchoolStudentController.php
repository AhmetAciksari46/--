<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Http\Requests\Profile\SchoolStudentUpdateProfileRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class SchoolStudentController extends Controller
{
    use ApiResponser;
    public function update(SchoolStudentUpdateProfileRequest $request)
    {
        $data = [];
        $user = $request->user();
        $updatedFields = [];

        // Sadece name doluysa onu güncelle
        if ($request->filled('name')) {
            $data['name'] = $request->name;
            $updatedFields[] = 'İsim';
        }

        // Sadece password doluysa hashleyip güncelle
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
            $updatedFields[] = 'şifre';
        }

        $user->update($data);
        if (empty($updatedFields)) {
            $message = 'Herhangi bir değişiklik yapılmadı.';
        } elseif (count($updatedFields) === 1) {
            $message = ucfirst($updatedFields[0]) . ' başarıyla güncellendi.';
        } else {
            $last = array_pop($updatedFields);
            $message = ucfirst(implode(', ', $updatedFields)) . ' ve ' . $last . ' başarıyla güncellendi.';
        }

        return $this->successResponse($user->fresh(), $message);
    }
}
