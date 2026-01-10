<?php

namespace App\Http\Controllers\School\User;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use app\Models\SchoolStudentProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\School;
use App\Models\User;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\ResetStudentPasswordRequest;
use App\Traits\ApiResponser;
use App\Models\ClassModel;
use Illuminate\Support\Facades\DB;
use App\Models\AdditionalClassRoom;
use Spatie\Permission\Models\Role;
use App\Http\Resources\StudentListResource;

/**
 * @OA\Tag(
 *     name="Manager & Teacher - Student Profil İşlemleri",
 *     description="Okul yöneticisinin öğrenci yönetim işlemleri"
 * )
 */

class SchoolStudentProfileController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/students",
     *     summary="Okula bağlı tüm öğrencileri listeler",
     *     tags={"Manager & Teacher - Student Profil İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List of students")
     * )
     */
    public function index(School $school)
    {
        if (!auth()->user()->can('student.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        $students = User::query()
            ->where('role', 'schoolstudent')
            ->whereHas('schoolStudentProfile', fn($q) => $q->where('school_id', $school->id))
            ->select(['id', 'name', 'userName']) // ✅ minimal user
            ->with([
                // ✅ sadece gereken alanlar
                'schoolStudentProfile:id,user_id,student_number,birth_date,img_path,status,is_active,active_class_id',
                'schoolStudentProfile.activeClass:id,name', // ✅ activeClass mini object
            ])
            ->get();

        if ($students->isEmpty()) {
            return $this->errorResponse('Bu okula ait öğrenci bulunamadı.', 200);
        }

        return $this->successResponse(
            StudentListResource::collection($students),
            'Öğrenciler başarıyla getirildi.',
            200
        );
    }

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/students/{student}",
     *     summary="Belirli öğrenci bilgilerini getirir",
     *     tags={"Manager & Teacher - Student Profil İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="student", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Student details")
     * )
     */
    public function show(School $school, User $student)
    {
        if (!auth()->user()->can('student.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        if (!$student->schoolStudentProfile || $student->schoolStudentProfile->school_id != $school->id) {
            return $this->errorResponse('Bu öğrenci bu okula ait değil.', 403);
        }

        if (!$student) {
            return $this->errorResponse('Öğrenci profili bulunamadı.', 404);
        }
        return $this->successResponse($student->load('schoolStudentProfile'), 'Öğrenci bilgileri getirildi.', 200);
    }

    /**
     * @OA\Post(
     *     path="/api/schools/{school}/students/",
     *     summary="Okula yeni öğrenci ekler",
     *     tags={"Manager & Teacher - Student Profil İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreStudentRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Student created")
     * )
     */
    public function store(StoreStudentRequest $request, School $school)
    {

        if (!auth()->user()->can('student.create')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        $data = $request->validated();

        // Eğer sınıf seçildiyse -> o sınıf gerçekten bu okula mı ait?
        if (!empty($data['active_class_id'])) {
            $classCheck = ClassModel::where('id', $data['active_class_id'])
                ->where('school_id', $school->id)
                ->exists();

            if (!$classCheck) {
                return $this->errorResponse('Bu sınıf bu okula ait değildir.', 422);
            }
        }

        DB::beginTransaction();

        try {

            // 1) User oluştur
            $user = User::create([
                'name' => $data['name'],
                'userName' => $data['userName'],
                'email' => $data['email'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => 'schoolstudent',
                'is_active' => true,
            ]);

            // 2) Role ata (Spatie)
            if (Role::where('name', 'school_student')->exists()) {
                $user->assignRole('school_student');
            }

            // 3) Student profile oluştur
            $profile = SchoolStudentProfile::create([
                'user_id' => $user->id,
                'school_id' => $school->id,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'birth_date' => $data['birth_date'],
                'student_number' => $data['student_number'],
                'tc_no' => $data['tc_no'],
                'gender' => $data['gender'] ?? null,
                'active_class_id' => $data['active_class_id'] ?? null, // <-- EKLENDİ
            ]);

            DB::commit();

            return $this->successResponse(
                $user->load('schoolStudentProfile'),
                'Yeni öğrenci başarıyla oluşturuldu.',
                200
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }
    }


    /**
     * @OA\Put(
     *     path="/api/schools/{school}/students/{student}",
     *     summary="Öğrenci bilgilerini günceller",
     *     description="Öğrenci user bilgileri (name,email,userName,is_active) ve profil bilgileri tek payload içinde güncellenir. Alanlar opsiyoneldir (partial update).",
     *     tags={"Manager & Teacher - Student Profil İşlemleri"},
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
     *         name="student",
     *         in="path",
     *         required=true,
     *         description="Öğrenci User ID",
     *         @OA\Schema(type="integer", example=8)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Güncellenecek alanları gönderin (partial update).",
     *         @OA\JsonContent(ref="#/components/schemas/UpdateStudentRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Öğrenci başarıyla güncellendi.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Öğrenci başarıyla güncellendi."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=8),
     *                 @OA\Property(property="name", type="string", example="Mehmet Can Demir"),
     *                 @OA\Property(property="email", type="string", example="mehmetcan@example.com"),
     *                 @OA\Property(property="role", type="string", example="schoolstudent"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="school_student_profile", type="object",
     *                     @OA\Property(property="id", type="integer", example=3),
     *                     @OA\Property(property="user_id", type="integer", example=8),
     *                     @OA\Property(property="school_id", type="integer", example=1),
     *                     @OA\Property(property="phone", type="string", example="+905554445566"),
     *                     @OA\Property(property="address", type="string", example="Ankara, Türkiye"),
     *                     @OA\Property(property="birth_date", type="string", format="date", example="2014-05-20"),
     *                     @OA\Property(property="gender", type="string", example="male")
     *                 )
     *             ),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Yetkisiz işlem veya öğrenci okulunuza ait değil.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Bu işlem için yetkiniz yok."),
     *             @OA\Property(property="code", type="integer", example=403)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation hatası",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Okul veya öğrenci bulunamadı"
     *     )
     * )
     */
    public function update(UpdateStudentRequest $request, School $school, User $student)
    {
        if (!auth()->user()->can('student.update')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        if (!$student->schoolStudentProfile || $student->schoolStudentProfile->school_id != $school->id) {
            return $this->errorResponse('Bu öğrenci bu okula ait değil.', 403);
        }

        $validated = $request->validated(); // ✅ burada al

        // ✅ active_class_id kontrolü (varsa)
        if (!empty($validated['active_class_id'])) {
            $classCheck = ClassModel::where('id', $validated['active_class_id'])
                ->where('school_id', $school->id)
                ->exists();

            if (!$classCheck) {
                return response()->json(['message' => 'Bu sınıf bu okula ait değildir.'], 422);
            }
        }

        try {
            // ✅ User alanları
            $userFields = Arr::only($validated, ['name', 'email', 'userName', 'is_active']);

            // ✅ Profile alanları
            $profileFields = Arr::except($validated, ['name', 'email', 'userName', 'is_active']);

            if (!empty($userFields)) {
                $student->update($userFields);
            }

            if (!empty($profileFields)) {
                $student->schoolStudentProfile->update($profileFields);
            }

            return $this->successResponse(
                $student->load('schoolStudentProfile'),
                'Öğrenci başarıyla güncellendi.',
                200
            );
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/schools/{school}/students/{student}",
     *     summary="Öğrenciyi siler",
     *     tags={"Manager & Teacher - Student Profil İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(response=200, description="Student deleted")
     * )
     */
    public function destroy(School $school, User $student)
    {
        if (!auth()->user()->can('student.delete')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        if (!$student->schoolStudentProfile || $student->schoolStudentProfile->school_id != $school->id) {
            return $this->errorResponse('Bu öğrenci bu okula ait değil.', 403);
        }

        $student->delete();

        return $this->successResponse(null, 'Öğrenci başarıyla silindi.', 200);
    }

    /**
     * @OA\Put(
     *     path="/api/schools/{school}/students/{student}/reset-password",
     *     summary="Öğrencinin şifresini sıfırlar",
     *     tags={"Manager & Teacher - Student Profil İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ResetStudentPasswordRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Password reset")
     * )
     */
    public function resetPassword(ResetStudentPasswordRequest $request, School $school, User $student)
    {
        if (!auth()->user()->can('student.update')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }
        if (!$student->schoolStudentProfile || $student->schoolStudentProfile->school_id != $school->id) {
            return $this->errorResponse('Bu öğrenci bu okula ait değil.', 403);
        }

        $student->password = Hash::make($request->password);
        $student->save();

        return $this->successResponse(null, 'Öğrencinin şifresi başarıyla sıfırlandı.', 200);
    }

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/students/by-class/{classModel}",
     *     summary="Belirli sınıfa ait öğrencileri listeler",
     *     tags={"Manager & Teacher - Student Profil İşlemleri"},
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
     *         name="classModel",
     *         in="path",
     *         required=true,
     *         description="Sınıf ID",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Sınıfa ait öğrenci listesi",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/SchoolStudentProfile")
     *         )
     *     ),
     *
     *     @OA\Response(response=403, description="Yetkisiz erişim"),
     *     @OA\Response(response=404, description="Sınıf bulunamadı")
     * )
     */
    public function getByClassModel(School $school, ClassModel $classModel)
    {
        if (!auth()->user()->can('student.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        // Sınıf okul doğrulaması
        if ($classModel->school_id !== $school->id) {
            return $this->errorResponse('Bu sınıf bu okula ait değil.', 403);
        }

        // Öğrencileri çek
        $students = SchoolStudentProfile::where('school_id', $school->id)
            ->where('active_class_id', $classModel->id)
            ->with('user')
            ->get();

        return $this->successResponse(
            $students,
            'Sınıfa ait öğrenciler başarıyla listelendi.',
            200
        );
    }


    /**
     * @OA\Get(
     *     path="/api/schools/{school}/students/{student}/details",
     *     summary="Belirli öğrenci bilgilerini getirir",
     *     tags={"Manager & Teacher - Student Profil İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="student", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Student details")
     * )
     */
    public function getDetails(School $school, User $student)
    {
        if (!auth()->user()->can('student.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        if (!$student->schoolStudentProfile || $student->schoolStudentProfile->school_id != $school->id) {
            return $this->errorResponse('Bu öğrenci bu okula ait değil.', 403);
        }

        if (!$student) {
            return $this->errorResponse('Öğrenci profili bulunamadı.', 404);
        }
        $data = $student->load(['schoolStudentProfile', 'schoolStudentProfile.activeClass', 'schoolStudentProfile.activeCourse', 'schoolStudentProfile.school', 'schoolStudentProfile.parents', 'schoolStudentProfile.healthProfile']);
        return $this->successResponse($data, 'Öğrenci bilgileri getirildi.', 200);
    }
}
