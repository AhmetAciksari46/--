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
use Illuminate\Support\Facades\DB;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\SchoolStudentRegisterRequest;
use App\Traits\ApiResponser; // <<< Bu satırı ekleyin!
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Bunu ekle

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="Kullanıcı kimlik doğrulama işlemleri"
 * )
 */
class AuthController extends Controller
{
    use ApiResponser;

    use AuthorizesRequests;
    //  Kayıt
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Yeni kullanıcı kaydı oluşturur",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="Ahmet Açısarı"),
     *             @OA\Property(property="email", type="string", example="ahmet@example.com"),
     *             @OA\Property(property="password", type="string", example="12345678")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Kullanıcı başarıyla oluşturuldu",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=400, description="Geçersiz veri gönderildi")
     * )
     */
    public function register(RegisterRequest $request)
    {

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
    /**
     * @OA\Post(
     *     path="/api/managerregister",
     *     summary="Manager kullanıcı kaydı",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","userName","password"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="userName", type="string"),
     *             @OA\Property(property="password", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Manager kaydı başarıyla oluşturuldu"),
     *     @OA\Response(response=500, description="Kayıt başarısız")
     * )
     */

    public function managerregister(RegisterRequest $request)
    {
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
    /**
     * @OA\Post(
     *     path="/api/teacherregister",
     *     summary="Teacher kullanıcı kaydı",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","userName","password"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="userName", type="string"),
     *             @OA\Property(property="password", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Teacher kaydı başarıyla oluşturuldu"),
     *     @OA\Response(response=500, description="Kayıt başarısız")
     * )
     */
    public function teacherregister(RegisterRequest $request)
    {

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

    public function createTeacher(RegisterRequest $request)
    {
        $this->authorize('createTeacher', [User::class, $request]);
    }

    /**
     * @OA@Post(
     *     path="/api/schoolstudentregister",
     *     summary="School Student kaydı",
     *     tags={"Auth"},  
     *     @OA\Response(response=201, description="School student kaydı başarıyla oluşturuldu"),
     *     @OA\Response(response=500, description="Kayıt başarısız")
     * )
     */
    public function schoolstudentregister(SchoolStudentRegisterRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = User::create([
                "name" => $request->name,
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


    /**
     * @OA\Post(
     *     path="/api/invidualstudentregister",
     *     summary="Individual Student kaydı",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","userName","password"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="userName", type="string"),
     *             @OA\Property(property="password", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Individual student kaydı başarıyla oluşturuldu"),
     *     @OA\Response(response=500, description="Kayıt başarısız")
     * )
     */
    public function invidualstudentregister(RegisterRequest $request)
    {
        DB::beginTransaction();
        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "userName" => $request->userName,
            "password" => Hash::make($request->password),
            "role" => 'individualstudent'
        ]);

        $token = $user->createToken("api_token")->plainTextToken;
        return response()->json([
            "user" => $user,
            "token" => $token
        ], 201);
    }




    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Kullanıcı girişi yapar ve erişim token'ı döner",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"login","password"},
     *             @OA\Property(property="login", type="string", example="root@root.com"),
     *             @OA\Property(property="password", type="string", example="root")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Giriş başarılı",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="1|abcdef1234567890"),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Geçersiz kimlik bilgileri")
     * )
     */

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






    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Aktif kullanıcı oturumunu kapatır",
     *     security={{"sanctum":{}}},
     *     tags={"Auth"},
     *     @OA\Response(response=200, description="Çıkış başarılı"),
     *     @OA\Response(response=401, description="Yetkilendirme gerekli")
     * )
     */

    // ✅ Çıkış
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(["message" => "Çıkış yapıldı"]);
    }
}
