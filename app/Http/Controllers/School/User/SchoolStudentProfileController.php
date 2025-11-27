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
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }

        $students = User::where('role', 'schoolstudent')
            ->whereHas('schoolStudentProfile', fn($q) => $q->where('school_id', $school->id))
            ->with('schoolStudentProfile')
            ->get();
        if ($students->isEmpty()) {
            return $this->successResponse([], 'Bu okula ait öğrenci bulunamadı.', 200);
        }
        return $this->successResponse($students, 'Öğrenciler başarıyla getirildi.', 200);
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
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
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
     *     path="/api/schools/{school}/students",
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
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }

        $data = $request->validated();
        // CLASS OKULA AİT Mİ?

        if (!empty($data['active_class_id'])) {
            $classCheck = ClassModel::where('id', $data['active_class_id'])
                ->where('school_id', $school->id)
                ->exists();

            if (!$classCheck) {
                return response()->json(['message' => 'Bu sınıf bu okula ait değildir.'], 422);
            }
        }






        try {
            // 1) User
            $user = User::create([
                'name' => $data['name'],
                'userName' => $data['userName'],
                'email' => $data['email'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => 'schoolstudent',
                "is_active" => true,
            ]);

            if (Role::where('name', 'school_student')->exists()) {
                $user->assignRole('school_student');
            }

            SchoolStudentProfile::create([
                'user_id' => $user->id,
                'school_id' => $school->id,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'birth_date' => $data['birth_date'],
                'student_number' => $data['student_number'],
                'tc_no' => $data['tc_no'],
                'gender' => $data['gender'] ?? null,
            ]);


            DB::commit();

            return $this->successResponse(
                $user->load('schoolStudentProfile'),
                'Yeni Öğrenci başarıyla oluşturuldu.',
                201
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorResponse('Öğrenci oluşturulurken hata: ' . $e->getMessage(), 500);
        }



        return $this->successResponse($student, 'Öğrenci başarıyla oluşturuldu.', 201);
    }

    /**
     * @OA\Put(
     *     path="/api/schools/{school}/students/{student}",
     *     summary="Öğrenci bilgilerini günceller",
     *     tags={"Manager & Teacher - Student Profil İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="student", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateStudentRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Student updated")
     * )
     */
    public function update(UpdateStudentRequest $request, School $school, User $student)
    {
        if (!auth()->user()->can('student.update')) {
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }
        if (!$student->schoolStudentProfile || $student->schoolStudentProfile->school_id != $school->id) {
            return $this->errorResponse('Bu öğrenci bu okula ait değil.', 403);
        }
        if (!empty($validated['active_class_id'])) {
            $classCheck = ClassModel::where('id', $validated['active_class_id'])
                ->where('school_id', $school->id)
                ->exists();

            if (!$classCheck) {
                return response()->json(['message' => 'Bu sınıf bu okula ait değildir.'], 422);
            }
        }




        $validated = $request->validated();

        $userFields = array_filter($validated, fn($k) => in_array($k, ['name', 'email']), ARRAY_FILTER_USE_KEY);
        $profileFields = array_filter($validated, fn($k) => !in_array($k, ['name', 'email']), ARRAY_FILTER_USE_KEY);

        if ($userFields) {
            $student->update($userFields);
        }

        if ($profileFields) {
            $student->schoolStudentProfile->update($profileFields);
        }

        return $this->successResponse($student->load('schoolStudentProfile'), 'Öğrenci başarıyla güncellendi.', 200);
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
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
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
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }
        if (!$student->schoolStudentProfile || $student->schoolStudentProfile->school_id != $school->id) {
            return response()->json(['message' => 'Bu öğrenci bu okula ait değil.'], 403);
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
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }

        // Sınıf okul doğrulaması
        if ($classModel->school_id !== $school->id) {
            return response()->json(['message' => 'Bu sınıf bu okula ait değil.'], 403);
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
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
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
