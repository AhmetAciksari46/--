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
use Spatie\Permission\Models\Role;
use App\Services\PermissionResolver;
use App\Services\Auth\PermissionSnapshotService;

/**
 * @OA\Tag(
 *     name="01 Auth İşlemleri",
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
     *     tags={"01 Auth İşlemleri"},
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
     *     path="/api/admin/managerregister",
     *     tags={"01 Auth İşlemleri"},
     *     summary="Yeni Manager kaydı oluştur (Sadece Admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","userName","email","password"},
     *             @OA\Property(property="name", type="string", example="Yönetici Adı"),
     *             @OA\Property(property="userName", type="string", example="manager1"),
     *             @OA\Property(property="email", type="string", example="manager@okul.com"),
     *             @OA\Property(property="password", type="string", example="123456"),
     *             @OA\Property(property="password_confirmation", type="string", example="123456"),
     *             @OA\Property(property="phone", type="string", example="+905551234567"),
     *             @OA\Property(property="address", type="string", example="İstanbul, Türkiye"),
     *             @OA\Property(property="birth_date", type="string", format="date", example="1985-05-10"),
     *             @OA\Property(property="referance", type="string", example="REF001")
     *         )
     *     ),
     *      @OA\Response(
     *         response=201,
     *         description="Manager başarıyla oluşturuldu.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Yeni Manager başarıyla oluşturuldu."),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/ManagerProfile"
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Yetkisiz erişim"),
     *     @OA\Response(response=422, description="Doğrulama hatası")
     * )
     */


    public function managerregister(RegisterRequest $request)
    {
        $this->authorizeRole(['admin']); // sadece admin

        // İstersen buraya ayrı bir FormRequest de koyabiliriz
        $request->validate([
            'name'      => 'required|string|max:255',
            'userName'  => 'required|string|max:255|unique:users,userName',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6',
            'phone'     => 'nullable|string|max:20',
            'address'   => 'nullable|string|max:255',
            'birth_date' => 'nullable|date|before:today',
            'referance' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // 1) User
            $user = User::create([
                'name'      => $request->name,
                'userName'  => $request->userName,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'role'      => 'manager',
                'is_active' => true,
            ]);

            // 2) Spatie rol
            if (Role::where('name', 'manager')->exists()) {
                $user->assignRole('manager');
            }

            // 3) ManagerProfile
            ManagerProfile::create([
                'user_id'          => $user->id,
                'phone'            => $request->phone,
                'address'          => $request->address,
                'referance'        => $request->referance,
                'birth_date'       => $request->birth_date,
                'payment_reminder' => false,
                // 'school_id' => null, // nullable; istersen burada hiç göndermeyelim
            ]);

            DB::commit();

            return $this->successResponse(
                $user->load('managerProfile'),
                'Yeni Manager başarıyla oluşturuldu.',
                201
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorResponse('Manager oluşturulurken hata: ' . $e->getMessage(), 500);
        }
    }
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Kullanıcı girişi yapar ve erişim token'ı döner",
     *     tags={"01 Auth İşlemleri"},
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
    public function login(Request $request, PermissionSnapshotService $permissionSnapshotService)
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
            return $this->errorResponse('Geçersiz kimlik bilgileri.Lütfen kullanıcı adınızı/e-posta adresinizi ve şifrenizi kontrol edin.', 401);
        }

        // 4. BAŞARILI GİRİŞ
        $user = Auth::user();
        $permissions = app(PermissionResolver::class)->resolve($user);
        $permissionSnapshot = $permissionSnapshotService->build($user);

        // Zaten 'api_token' kullanıyorsunuz, bu iyi bir yöntem.
        $token = $user->createToken("api_token")->plainTextToken;

        return $this->successResponse([
            "token" => $token,
            'user' => [
                'id'        => $user->id,
                'name'      => $user->name,
                'userName'  => $user->userName,
                'email'     => $user->email,
                'role'      => $user->role, // sadece label
                'is_active' => $user->is_active,
            ],
            "permissionSnapshot" => $permissionSnapshot
        ], 'Giriş başarılı.', 200);
    }





    /**
     * @OA\Get(
     *     path="/api/me",
     *     summary="Giriş yapan kullanıcının bilgilerini ve permission snapshot'ını döner",
     *     tags={"01 Auth İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Kullanıcı bilgileri ve permission snapshot döndürüldü",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Bilgiler optimize edildi."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Super Admin"),
     *                     @OA\Property(property="userName", type="string", example="root"),
     *                     @OA\Property(property="email", type="string", example="root@root.com"),
     *                     @OA\Property(property="role", type="string", example="admin"),
     *                     @OA\Property(property="is_active", type="boolean", example=true)
     *                 ),
     *                 @OA\Property(
     *                     property="permissionSnapshot",
     *                     type="array",
     *                     description="Frontend için permission listesi (snapshot)",
     *                     @OA\Items(type="string", example="teacher.view.detail"),
     *                     example={"school.view","teacher.view","teacher.view.detail","studentpreregistration.approve"}
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - token yok veya geçersiz",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */

    public function meendpoint(Request $request)
    {
        $user = Auth::user();
        $permissionSnapshotService = app(PermissionSnapshotService::class);
        $permissionSnapshot = $permissionSnapshotService->build($user);
        return $this->successResponse([
            'user' => [
                'id'        => $user->id,
                'name'      => $user->name,
                'userName'  => $user->userName,
                'email'     => $user->email,
                'role'      => $user->role, // sadece label
                'is_active' => $user->is_active,
            ],
            "permissionSnapshot" => $permissionSnapshot
        ], 'Bilgiler optimize edildi.', 200);
    }
    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Aktif kullanıcı oturumunu kapatır",
     *     security={{"sanctum":{}}},
     *     tags={"01 Auth İşlemleri"},
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
