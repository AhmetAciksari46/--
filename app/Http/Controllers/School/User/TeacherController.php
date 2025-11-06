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
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="TeacherProfile",
 *     description="Öğretmenlerin kendi profil işlemleri"
 * )
 */
class TeacherController extends Controller
{
    use ApiResponser;
    /**
     * @OA\Get(
     *     path="/api/schools/{school}/teachers",
     *     summary="List teachers of a school",
     *     tags={"Teachers"},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List of teachers")
     * )
     */
    public function index($school)
    {
        $teachers = User::where('role', 'teacher')
            ->whereHas('teacherProfile', fn($q) => $q->where('school_id', $school))
            ->with('teacherProfile')
            ->get();

        return response()->json($teachers);
    }
    /**
     * @OA\Get(
     *     path="/api/schools/{school}/teachers/{teacher}",
     *     summary="Get teacher by ID",
     *     tags={"TeacherProfile"},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="teacher", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Teacher details")
     * )
     */
    public function show($school, User $teacher)
    {
        $teacher->load('teacherProfile');
        return response()->json($teacher);
    }
    /**
     * @OA\Post(
     *     path="/api/schools/{school}/teachers",
     *     summary="Create a new teacher",
     *     tags={"TeacherProfile"},
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","email","password","branch_id"},
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="userName", type="string"),
     *              @OA\Property(property="email", type="string"),
     *              @OA\Property(property="password", type="string"),
     *              @OA\Property(property="branch_id", type="integer"),
     *              @OA\Property(property="phone", type="string"),
     *              @OA\Property(property="address", type="string")
     *          )
     *     ),
     *     @OA\Response(response=201, description="Teacher created successfully")
     * )
     */
    public function store(Request $request, $school)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'userName' => 'required|string|unique:users',
            'email' => 'nullable|email|unique:users',
            'password' => 'required|string|min:6',
            'branch_id' => 'required|exists:branches,id',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $teacher = DB::transaction(function () use ($data, $school) {
            $user = User::create([
                'name' => $data['name'],
                'userName' => $data['userName'],
                'email' => $data['email'] ?? null,
                'password' => bcrypt($data['password']),
                'role' => 'teacher',
            ]);

            $profile = $user->teacherProfile()->create([
                'school_id' => $school,
                'branch_id' => $data['branch_id'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]);

            return $user->load('teacherProfile');
        });

        return response()->json($teacher, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/schools/{school}/teachers/{teacher}",
     *     summary="Update teacher information",
     *     tags={"TeacherProfile"},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="teacher", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *          required=false,
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="email", type="string"),
     *              @OA\Property(property="phone", type="string"),
     *              @OA\Property(property="address", type="string")
     *          )
     *     ),
     *     @OA\Response(response=200, description="Teacher updated successfully")
     * )
     */
    public function update(Request $request, $school, User $teacher)
    {
        $data = $request->only(['name', 'email', 'phone', 'address']);

        $teacher->update($data);
        $teacher->teacherProfile->update($data);

        return response()->json(['message' => 'Teacher updated successfully']);
    }

    /**
     * @OA\Delete(
     *     path="/api/schools/{school}/teachers/{teacher}",
     *     summary="Delete teacher",
     *     tags={"TeacherProfile"},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="teacher", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Teacher deleted successfully")
     * )
     */
    public function destroy($school, User $teacher)
    {
        $teacher->delete();
        return response()->json(['message' => 'Teacher deleted']);
    }

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/teachers/{teacher}/permissions",
     *     summary="Get teacher permissions",
     *     tags={"TeacherProfile"},
     *     @OA\Response(response=200, description="List of teacher permissions")
     * )
     */
    public function getPermissions(User $teacher)
    {
        $permissions = $teacher->getAllPermissions()->pluck('name');
        return response()->json(['permissions' => $permissions]);
    }

    /**
     * @OA\Put(
     *     path="/api/schools/{school}/teachers/{teacher}/permissions",
     *     summary="Update teacher permissions",
     *     tags={"TeacherProfile"},
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="permissions", type="array", @OA\Items(type="string"))
     *          )
     *     ),
     *     @OA\Response(response=200, description="Permissions updated successfully")
     * )
     */
    public function updatePermissions(Request $request, User $teacher)
    {
        // 1️⃣ Policy kontrolü: Manager bu öğretmeni düzenlemeye yetkili mi?
        $this->authorize('updatePermissions', $teacher);

        $data = $request->validate([
            'permissions' => 'required|array',
        ]);

        $teacher->syncPermissions($data['permissions']);

        return response()->json(['message' => 'Permissions updated']);
    }





    // /**
    //  * @OA\Put(
    //  *     path="/api/me/teacher/updateprofile",
    //  *     tags={"TeacherProfile"},
    //  *     summary="Öğretmen kendi kullanıcı hesabını günceller",
    //  *     security={{"bearerAuth": {}}},
    //  *     @OA\RequestBody(
    //  *         required=true,
    //  *         @OA\JsonContent(ref="#/components/schemas/UpdateProfileRequest")
    //  *     ),
    //  *     @OA\Response(
    //  *         response=200,
    //  *         description="Profil bilgileri başarıyla güncellendi",
    //  *         @OA\JsonContent(ref="#/components/schemas/User")
    //  *     ),
    //  *     @OA\Response(response=401, description="Yetkisiz erişim"),
    //  *     @OA\Response(response=422, description="Doğrulama hatası")
    //  * )
    //  */
    // public function update(UpdateProfileRequest $request)
    // {
    //     $user = $request->user();
    //     $validated = $request->validated();

    //     $updatedFields = [];
    //     if (isset($validated['name']) && $user->name !== $validated['name']) {
    //         $user->name = $validated['name'];
    //         $updatedFields[] = 'İsim';
    //     }

    //     if (isset($validated['userName']) && $user->userName !== $validated['userName']) {
    //         $user->userName = $validated['userName'];
    //         $updatedFields[] = 'kullanıcı adı';
    //     }

    //     if (isset($validated['email']) && $user->email !== $validated['email']) {
    //         $user->email = $validated['email'];
    //         $user->email_verified_at = null;
    //         $updatedFields[] = 'e-posta adresi';
    //     }

    //     if (!empty($validated['new_password'])) {
    //         $user->password = $validated['new_password']; // Hashed cast sayesinde otomatik hashlenir
    //         $updatedFields[] = 'şifre';
    //     }

    //     $user->save();

    //     if (in_array('e-posta adresi', $updatedFields)) {
    //         $user->sendEmailVerificationNotification();
    //     }

    //     if (empty($updatedFields)) {
    //         $message = 'Herhangi bir değişiklik yapılmadı.';
    //     } elseif (count($updatedFields) === 1) {
    //         $message = ucfirst($updatedFields[0]) . ' başarıyla güncellendi.';
    //     } else {
    //         $last = array_pop($updatedFields);
    //         $message = ucfirst(implode(', ', $updatedFields)) . ' ve ' . $last . ' başarıyla güncellendi.';
    //     }

    //     return $this->successResponse($user->fresh(), $message);
    // }

    // public function updatebyid(Request $request, $teacherId) //for admin
    // {}
    // public function updateprofilesettingsbyid(Request $request, $teacherId) //for admin
    // {}
}
