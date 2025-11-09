<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ApiResponser; // <<< Bu satırı ekleyin!
/**
 * @OA\Tag(
 *     name="User",
 *     description="Kullanıcı işlemleri"
 * )
 */
class UserController extends Controller
{
    use ApiResponser; // <<< Trait'i kullanıma alın!
    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Kullanıcı listesini döndürür",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}}, 
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı",
     *     )
     * )
     */
    public function index() // tüm kullanıcılar
    {
        $this->authorizeRole(['admin', 'manager']);
        $users = User::with([
            'individualStudentProfile',
            'schoolStudentProfile',
            'teacherProfile',
            'managerProfile'
        ])->get();
        return $this->successResponse($users);
    }
    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Tek kullanıcı bilgisi",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}}, 
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Kullanıcı ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Başarılı"),
     *     @OA\Response(response=404, description="Kullanıcı bulunamadı")
     * )
     */
    public function show($id) // tek kullanıcı
    {
        $this->authorizeRole(['admin', 'manager', 'teacher']);
        $user = User::with([
            'individualStudentProfile:id,user_id',
            'schoolStudentProfile:id,user_id',
            'teacherProfile:id,user_id',
            'managerProfile:id,user_id'
        ])->findOrFail($id);






        if (!$user) {
            return $this->errorResponse('user_not_found', 404);
        }
        return $this->successResponse($user, 'Kullanıcı bilgileri başarıyla alındı.');
    }
    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Yeni kullanıcı oluştur",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}}, 
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="userName", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="role", type="string"),
     *             @OA\Property(property="is_active", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Kullanıcı oluşturuldu")
     * )
     */
    public function store(Request $request) // yeni kullanıcı
    {
        $this->authorizeRole(['admin', 'manager']);
        $user = User::create($request->all());
        return response()->json($user, 201);
    }
    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Kullanıcıyı güncelle",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}}, 
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Kullanıcı ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="userName", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="role", type="string"),
     *             @OA\Property(property="is_active", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Başarılı")
     * )
     */
    public function update(Request $request, $id)
    {
        $this->authorizeRole(['admin', 'manager']);
        $user = User::findOrFail($id);
        $user->update($request->all());
        return response()->json($user);
    }
    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Kullanıcıyı sil",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}}, 
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Kullanıcı ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Başarılı")
     * )
     */
    public function destroy($id)
    {
        $this->authorizeRole(['admin']);
        User::destroy($id);
        return response()->json(null, 204);
    }
}
