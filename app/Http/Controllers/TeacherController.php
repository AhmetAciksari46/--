<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Traits\ApiResponser;
use App\Http\Requests\Profile\UpdateProfileRequest;

class TeacherController extends Controller
{
    use ApiResponser;

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();

        $updatedFields = [];
        if (isset($validated['name']) && $user->name !== $validated['name']) {
            $user->name = $validated['name'];
            $updatedFields[] = 'İsim';
        }

        if (isset($validated['userName']) && $user->userName !== $validated['userName']) {
            $user->userName = $validated['userName'];
            $updatedFields[] = 'kullanıcı adı';
        }

        if (isset($validated['email']) && $user->email !== $validated['email']) {
            $user->email = $validated['email'];
            $user->email_verified_at = null;
            $updatedFields[] = 'e-posta adresi';
        }

        if (!empty($validated['new_password'])) {
            $user->password = $validated['new_password']; // Hashed cast sayesinde otomatik hashlenir
            $updatedFields[] = 'şifre';
        }

        $user->save();

        if (in_array('e-posta adresi', $updatedFields)) {
            $user->sendEmailVerificationNotification();
        }

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

    public function updatebyid(Request $request, $teacherId) //for admin
    {}
    public function updateprofilesettingsbyid(Request $request, $teacherId) //for admin
    {}
}
