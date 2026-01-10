<?php

namespace App\Http\Controllers\School\User;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Traits\ApiResponser;
use App\Http\Requests\Teacher\TeacherUpdateRequestManager;
use App\Http\Requests\Profile\UpdateProfileRequest;
use Illuminate\Support\Facades\DB;
use App\Models\School;
use Spatie\Permission\Models\Role;
use App\Http\Resources\TeacherListResource;

/**
 * @OA\Tag(
 *     name="Manager & Teacher - Teacher İşlemleri",
 *     description="Öğretmenlerin kendi profil işlemleri"
 * )
 */
class TeacherController extends Controller
{
    use ApiResponser;




    /**
     * @OA\Get(
     *     path="/api/schools/{school}/teachers",
     *     summary="Belirli bir okulun öğretmenlerini listeler",
     *     tags={"Manager & Teacher - Teacher İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List of teachers")
     * )
     */
    public function index(School $school)
    {
        if (!auth()->user()->can('teacher.view.list')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        $teachers = User::query()
            ->where('role', 'teacher')
            ->whereHas('teacherProfile', fn($q) => $q->where('school_id', $school->id))
            ->with([
                'teacherProfile' => fn($q) => $q->select([
                    'id',
                    'user_id',
                    'phone',
                    'img_path',
                    'status',
                    'is_active',
                    'branch_id'
                ]),
                'teacherProfile.branch:id,name'
            ])
            ->select(['id', 'name', 'userName'])
            ->get();

        if ($teachers->isEmpty()) {
            return $this->errorResponse('Bu okula ait öğretmen bulunamadı.', 404);
        }
        return $this->successResponse(TeacherListResource::collection($teachers), 'Okula ait öğretmenler getirildi.', 200);
    }
    /**
     * @OA\Get(
     *     path="/api/schools/{school}/teachers/{teacher}",
     *     summary="Belirli öğretmeni getirir",
     *     tags={"Manager & Teacher - Teacher İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         description="Okul ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="teacher",
     *         in="path",
     *         required=true,
     *         description="Öğretmen ID",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\Response(response=200, description="Teacher details")
     * )
     */
    public function show(School $school, User $teacher)
    {
        if (!auth()->user()->can('teacher.view.detail')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }

        if (!$teacher->teacherProfile || $teacher->teacherProfile->school_id != $school->id) {

            return $this->errorResponse('Bu öğretmen bu okula ait değil.', 403);
        }

        $teacher->load('teacherProfile');
        return $this->successResponse($teacher, 'Öğretmen bilgileri getirildi.', 200);
    }
    /**
     * @OA\Post(
     *     path="/api/schools/{school}/teachers",
     *     summary="Belirli okula yeni öğretmen ekler",
     *     tags={"Manager & Teacher - Teacher İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         description="Okul ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","userName","password","branch_id"},
     *             @OA\Property(property="name", type="string", example="Ali Yılmaz"),
     *             @OA\Property(property="userName", type="string", example="aliyilmaz"),
     *             @OA\Property(property="email", type="string", example="ali@example.com"),
     *             @OA\Property(property="password", type="string", example="12345678"),
     *             @OA\Property(property="password_confirmation", type="string", example="12345678"),
     *             @OA\Property(property="branch_id", type="integer", example=2),
     *             @OA\Property(property="phone", type="string", example="+905551234567"),
     *             @OA\Property(property="address", type="string", example="İstanbul"),
     *             @OA\Property(property="birth_date", type="string", format="date", example="1985-05-10"),
     *             @OA\Property(property="referance", type="string", example="REF001")
     *         )
     *     ),
     *
     *     @OA\Response(response=201, description="Teacher created successfully")
     * )
     */
    public function store(Request $request, School $school)
    {
        if (!auth()->user()->can('teacher.create')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }

        try {
            $data = $request->validate([
                'name' => 'required|string',
                'userName' => 'required|string|unique:users,userName',
                'email' => 'nullable|email|unique:users,email',
                'password' => 'required|string|min:6',
                'branch_id' => 'required|exists:branches,id',
                'phone' => 'nullable|string',
                'address' => 'nullable|string',
                'birth_date' => 'nullable|date|before:today',
                'referance' => 'nullable|string|max:255',
            ]);

            return DB::transaction(function () use ($data, $school) {

                $user = User::create([
                    'name' => $data['name'],
                    'userName' => $data['userName'],
                    'email' => $data['email'] ?? null,
                    'password' => bcrypt($data['password']),
                    'role' => 'teacher',
                ]);

                $user->assignRole('teacher');

                $user->teacherProfile()->create([
                    'school_id' => $school->id,
                    'branch_id' => $data['branch_id'],
                    'birth_date' => $data['birth_date'] ?? null,
                    'referance' => $data['referance'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                ]);

                return $this->successResponse(
                    $user->load('teacherProfile'),
                    'Yeni öğretmen oluşturuldu.',
                    200
                );
            });
        } catch (\Exception $e) {
            return $this->errorResponse('Öğretmen oluşturulurken bir hata oluştu: ' . $e->getMessage(), 500);
        }
    }


    /**
     * @OA\Put(
     *     path="/api/schools/{school}/teachers/{teacher}",
     *     summary="Belirli öğretmeni günceller",
     *     tags={"Manager & Teacher - Teacher İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         description="Okul ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="teacher",
     *         in="path",
     *         required=true,
     *         description="Öğretmen User ID",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TeacherUpdateRequestManager")
     *     ),
     *
     *     @OA\Response(response=200, description="Teacher updated successfully")
     * )
     */

    public function update(TeacherUpdateRequestManager $request, School $school, User $teacher)
    {
        if (!auth()->user()->can('teacher.update')) {
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }

        // 🔒 Öğretmen bu okula mı ait?
        if (!$teacher->teacherProfile || $teacher->teacherProfile->school_id != $school->id) {

            return $this->errorResponse('Bu öğretmen bu okula ait değil.', 403);
        }
        try {
            $validated = $request->validated();

            // USER alanları
            $userFields = array_filter(
                $validated,
                fn($k) =>
                in_array($k, ['name', 'email']),
                ARRAY_FILTER_USE_KEY
            );

            // TEACHER PROFILE alanları
            $profileFields = array_filter(
                $validated,
                fn($k) =>
                !in_array($k, ['name', 'email']),
                ARRAY_FILTER_USE_KEY
            );

            if (!empty($userFields)) {
                $teacher->update($userFields);
            }

            if (!empty($profileFields)) {
                $teacher->teacherProfile->update($profileFields);
            }


            return $this->successResponse($teacher->load('teacherProfile'), 'Öğretmen başarıyla güncellendi.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse("öğretmen güncellenirken bir hata oluştu: " . $e->getMessage(), 500);
        }
    }
    public function updateTeacherUser(UpdateProfileRequest $request)
    {
        $user = Auth::user();

        if (!$user->isTeacher()) {
            return $this->errorResponse('Sadece teacherlar istek atabilir.', 404);
        }

        try {
            $validated = $request->validate([
                'name'      => 'sometimes|string|max:255',
                'userName'  => 'sometimes|string|max:255|unique:users,userName,' . $user->id,
                'email'     => 'sometimes|email|unique:users,email,' . $user->id,
                'password'  => 'sometimes|string|min:6|confirmed',
            ]);

            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }

            $user->update($validated);

            return $this->successResponse($user, 'Bilgileriniz başarıyla güncellendi.');
        } catch (\Exception $e) {
            return $this->errorResponse("teacher güncellenirken bir hata oluştu: " . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/schools/{school}/teachers/{teacher}",
     *     summary="Belirli öğretmeni siler",
     *     tags={"Manager & Teacher - Teacher İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         description="Okul ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="teacher",
     *         in="path",
     *         required=true,
     *         description="Öğretmen ID",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\Response(response=200, description="Teacher deleted")
     * )
     */
    public function destroy(School $school, User $teacher)
    {
        if (!auth()->user()->can('teacher.delete')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        // 🔒 Öğretmen bu okula mı ait?
        if ($teacher->teacherProfile->school_id != $school->id) {
            return $this->errorResponse('Bu öğretmen bu okula ait değil.', 403);
        }

        $teacher->delete();
        return $this->successResponse(null, 'Öğretmen Silindi', 200);
    }

    /**
     * @OA\Put(
     *     path="/api/schools/{school}/teachers/{teacher}/reset-password",
     *     summary="Manager öğretmenin şifresini sıfırlar",
     *     tags={"Manager & Teacher - Teacher İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="teacher",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 example="YeniSifre123!"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Şifre başarıyla sıfırlandı"),
     *     @OA\Response(response=403, description="Yetkisiz erişim"),
     *     @OA\Response(response=422, description="Valdasyon hatası")
     * )
     */
    public function resetPassword(Request $request, School $school, User $teacher)
    {
        if (!auth()->user()->can('teacher.update')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }

        // 🛡 2. Teacher doğru okula mı bağlı?
        if (!$teacher->teacherProfile || $teacher->teacherProfile->school_id !== $school->id) {
            return $this->errorResponse('Bu öğretmen bu okula ait değil.', 403);
        }

        // 4. Validation
        $validated = $request->validate([
            'password' => 'required|string|min:8|max:64'
        ]);

        // 5. Şifre sıfırlama
        $teacher->password = bcrypt($validated['password']);
        $teacher->save();

        return $this->successResponse(null, 'Öğretmenin şifresi başarıyla sıfırlandı.', 200);
    }
}

    // /**
    //  * @OA\Post(
    //  *     path="/api/admin/teachers/",
    //  *     summary="Belirli okula yeni öğretmen ekler",
    //  *     tags={"Admin - Teacher İşlemleri"},
    //  *     security={{"bearerAuth":{}}},
    //  *
    //  *
    //  *     @OA\RequestBody(
    //  *         required=true,
    //  *         @OA\JsonContent(
    //  *             required={"name","userName","password","branch_id"},
    //  *             @OA\Property(property="name", type="string", example="Ali Yılmaz"),
    //  *             @OA\Property(property="userName", type="string", example="aliyilmaz"),
    //  *             @OA\Property(property="email", type="string", example="ali@example.com"),
    //  *             @OA\Property(property="password", type="string", example="12345678"),
    //  *             @OA\Property(property="password_confirmation", type="string", example="12345678"),
    //  *             @OA\Property(property="branch_id", type="integer", example=2),
    //  *             @OA\Property(property="school_id", type="integer", example=1),
    //  *             @OA\Property(property="phone", type="string", example="+905551234567"),
    //  *             @OA\Property(property="address", type="string", example="İstanbul"),
    //  *             @OA\Property(property="birth_date", type="string", format="date", example="1985-05-10"),
    //  *             @OA\Property(property="referance", type="string", example="REF001")
    //  *         )
    //  *     ),
    //  *
    //  *     @OA\Response(response=201, description="Teacher created successfully")
    //  * )
    //  */
    // public function store(Request $request)
    // {
    //     $data = $request->validate(
    //         [
    //             'name' => 'required|string',
    //             'userName' => 'required|string|unique:users',
    //             'email' => 'nullable|email|unique:users',
    //             'password' => 'required|string|min:6',
    //             'branch_id' => 'required|exists:branches,id',
    //             'school_id' => 'required|exists:schools,id',
    //             'phone' => 'nullable|string',
    //             'address' => 'nullable|string',
    //             'birth_date' => 'nullable|date|before:today',
    //             'referance' => 'nullable|string|max:255',
    //         ],
    //         [
    //             'name.required' => 'Ad alanı zorunludur.',
    //             'name.string'   => 'Ad alanı geçerli bir metin olmalıdır.',

    //             'userName.required' => 'Kullanıcı adı zorunludur.',
    //             'userName.string'   => 'Kullanıcı adı geçerli bir metin olmalıdır.',
    //             'userName.unique'   => 'Bu kullanıcı adı zaten kullanılmaktadır.',

    //             'email.email'  => 'Lütfen geçerli bir e-posta adresi giriniz.',
    //             'email.unique' => 'Bu e-posta adresi zaten kayıtlıdır.',

    //             'password.required' => 'Şifre zorunludur.',
    //             'password.string'   => 'Şifre geçerli bir metin olmalıdır.',
    //             'password.min'      => 'Şifre en az :min karakter olmalıdır.',

    //             'branch_id.required' => 'Şube alanı zorunludur.',
    //             'branch_id.exists'   => 'Seçilen şube bulunamadı.',

    //             'school_id.required' => 'Okul alanı zorunludur.',
    //             'school_id.exists'   => 'Seçilen okul bulunamadı.',

    //             'phone.string'  => 'Telefon alanı geçerli bir metin olmalıdır.',

    //             'address.string' => 'Adres alanı geçerli bir metin olmalıdır.',

    //             'birth_date.date'   => 'Doğum tarihi geçerli bir tarih olmalıdır.',
    //             'birth_date.before' => 'Doğum tarihi bugünden ileri bir tarih olamaz.',

    //             'referance.string' => 'Referans alanı geçerli bir metin olmalıdır.',
    //             'referance.max'    => 'Referans en fazla :max karakter olabilir.',
    //         ]
    //     );
    //     $schoolId = $request->school_id;
    //     //TODO: Okul yoksa hata dönebilir.
    //     $teacher = DB::transaction(function () use ($data, $schoolId) {
    //         $user = User::create([
    //             'name' => $data['name'],
    //             'userName' => $data['userName'],
    //             'email' => $data['email'] ?? null,
    //             'password' => bcrypt($data['password']),
    //             'role' => 'teacher',
    //         ]);
    //         $user->assignRole('teacher');
    //         $profile = $user->teacherProfile()->create([
    //             'school_id' => $schoolId,
    //             'branch_id' => $data['branch_id'],
    //             'phone' => $data['phone'] ?? null,
    //             'address' => $data['address'] ?? null,
    //         ]);

    //         return $user->load('teacherProfile');
    //     });

    //     return $this->successResponse($teacher, 'Yeni öğretmen oluşturuldu.', 200);
    // }