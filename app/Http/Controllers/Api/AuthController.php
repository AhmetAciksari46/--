<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\ManagerProfile;
use App\Models\TeacherProfile;
use App\Models\InvidualStudentProfile;
use App\Models\SchoolStudentProfile;

class AuthController extends Controller
{
    // ✅ Kayıt
    public function register(Request $request)
    {
        $request->validate([
            "name" => "required|string|max:255",
            "userName" => "required|string|max:255|unique:users",
            "email" => "required|string|email",
            "password" => "required|string|min:6|confirmed"
        ]);
        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "userName" => $request->userName,
            "password" => Hash::make($request->password)
        ]);

        $token = $user->createToken("api_token")->plainTextToken;
        return response()->json([
            "user" => $user,
            "token" => $token
        ], 201);
    }
    public function managerregister(Request $request)
    {
        $request->validate([
            "name" => "required|string|max:255",
            "userName" => "required|string|max:255|unique:users",
            "email" => "required|string|email",
            "password" => "required|string|min:6|confirmed",
        ]);
            DB::beginTransaction();

        try {
           $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "userName" => $request->userName,
            "password" => Hash::make($request->password),
            "role" => 'manager'
        ]);
        DB::commit();
            $token = $user->createToken("api_token")->plainTextToken;
        return response()->json([
            "user" => $user,
            "token" => $token
        ], 201);

        } catch (\Exception $e) {
          DB::rollBack();
        return response()->json(["message" => "Yönetici kaydı başarısız oldu."], 500);
        }
       
    }
    public function teacherregister(Request $request)
    {
        $request->validate([
            "name" => "required|string|max:255",
            "userName" => "required|string|max:255|unique:users",
            "email" => "required|string|email",
            "password" => "required|string|min:6|confirmed",
        ]);
            DB::beginTransaction();

        try {
           $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "userName" => $request->userName,
            "password" => Hash::make($request->password),
            "role" => 'teacher'
        ]);
        DB::commit();
            $token = $user->createToken("api_token")->plainTextToken;
        return response()->json([
            "user" => $user,
            "token" => $token
        ], 201);

        } catch (\Exception $e) {
          DB::rollBack();
        return response()->json(["message" => "Öğretmen kaydı başarısız oldu."], 500);
        }
       
    }


public function schoolstudentregister(Request $request)
    {
        $request->validate([
            "name" => "required|string|max:255",
            "userName" => "required|string|max:255|unique:users",
            "password" => "required|string|min:6|confirmed",
        ]);
            DB::beginTransaction();

        try {
           $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "userName" => $request->userName,
            "password" => Hash::make($request->password),
            "role" => 'schoolstudent'
        ]);
        DB::commit();
            $token = $user->createToken("api_token")->plainTextToken;
        return response()->json([
            "user" => $user,
            "token" => $token
        ], 201);

        } catch (\Exception $e) {
          DB::rollBack();
        return response()->json(["message" => "Öğrenci kaydı başarısız oldu."], 500);
        }
       
    }


    //-------------------------------

public function invidualstudentregister(Request $request)
    {
        $request->validate([
            "name" => "required|string|max:255",
            "userName" => "required|string|max:255|unique:users",
            "email" => "required|string|email",
            "password" => "required|string|min:6|confirmed",
        ]);
            DB::beginTransaction();

        try {
           $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "userName" => $request->userName,
            "password" => Hash::make($request->password),
            "role" => 'individualstudent'
        ]);
        DB::commit();
            $token = $user->createToken("api_token")->plainTextToken;
        return response()->json([
            "user" => $user,
            "token" => $token
        ], 201);

        } catch (\Exception $e) {
          DB::rollBack();
        return response()->json(["message" => "Öğrenci kaydı başarısız oldu."], 500);
        }
       
    }

    //-------------------------------




    // ✅ Giriş
 public function login(Request $request)
{
    // 1. DOĞRULAMA (VALIDATION)
    // email veya userName'den en az biri zorunlu olmalı, password zorunlu.
    $request->validate([
        "login" => "required|string", // Kullanıcının girdiği 'email' veya 'userName' değeri
        "password" => "required|string"
    ]);

    // Kullanıcının girdiği değeri (e-posta mı yoksa kullanıcı adı mı olduğunu) belirleme
    $loginValue = $request->input('login');
    $fieldType = filter_var($loginValue, FILTER_VALIDATE_EMAIL) ? 'email' : 'userName';

    // 2. KİMLİK BİLGİLERİNİ HAZIRLAMA
    $credentials = [
        $fieldType => $loginValue,
        'password' => $request->input('password')
    ];

    // 3. GİRİŞ DENEMESİ (AUTHENTICATION ATTEMPT)
    if (!Auth::attempt($credentials)) {
        return response()->json([
            "message" => "Giriş bilgileri hatalı. Lütfen kullanıcı adınızı/e-posta adresinizi ve şifrenizi kontrol edin."
        ], 401);
    }

    // 4. BAŞARILI GİRİŞ
    $user = Auth::user();
    
    // Zaten 'api_token' kullanıyorsunuz, bu iyi bir yöntem.
    $token = $user->createToken("api_token")->plainTextToken; 

    return response()->json([
        "user" => $user,
        "token" => $token
    ]);
}

    // ✅ Çıkış
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(["message" => "Çıkış yapıldı"]);
    }

    // ✅ Kullanıcı Bilgisi
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
