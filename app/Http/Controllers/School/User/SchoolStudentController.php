<?php

namespace App\Http\Controllers\School\User;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Http\Requests\Profile\SchoolStudentUpdateProfileRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\School;
use App\Models\ClassModel;
use App\Models\SchoolStudentProfile;
use App\Models\User;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\CompleteStudentProfileRequest;
use Illuminate\Support\Facades\DB;
use App\Models\StudentParent;
use App\Models\StudentHealthProfile;
use App\Http\Requests\Student\DeleteStudentRequest;

/**
 * @OA\Tag(
 *     name="SchoolStudentProfile",
 *     description="Okul öğrencilerinin profil işlemleri"
 * )
 */

class SchoolStudentController extends Controller
{
    //TODO :ADDİTİONAL CLASSROOM İÇİN AYNI ŞEY YAPILACAK.


    use ApiResponser;
    /**
     * @OA\Post(
     *     path="/api/schools/{school}/students/createuser",
     *     summary="Yeni öğrenci oluştur (sadece User tablosuna kaydeder)",
     *     tags={"SchoolStudentProfile"},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StoreStudentRequest")),
     *     @OA\Response(response=201, description="Öğrenci başarıyla oluşturuldu"),
     *     @OA\Response(response=403, description="Yetkisiz işlem")
     * )
     */
    public function store(StoreStudentRequest $request, $school)
    {
        $classModel = ClassModel::find($request->input('class_model_id'));

        $this->authorize('create', [\App\Models\ClassModel::class, $classModel]);

        $data = $request->validated();

        $student = User::create([
            'name' => $data['name'],
            'userName' => $data['userName'],
            'email' => $data['email'] ?? null,
            'password' => bcrypt($data['password']),
            'role' => 'schoolstudent',
        ]);

        return response()->json([
            'message' => 'Öğrenci kullanıcı kaydı başarıyla oluşturuldu.',
            'student' => $student
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/schools/{school}/students/{user}/completeprofile",
     *     summary="Öğrencinin profil, veli ve sağlık bilgilerini tamamlar",
     *     tags={"SchoolStudentProfile"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/CompleteStudentProfileRequest")),
     *     @OA\Response(response=201, description="Profil başarıyla oluşturuldu")
     * )
     */
    public function completeProfile(CompleteStudentProfileRequest $request, $school, User $user)
    {
        DB::transaction(function () use ($request, $user) {
            $profile = SchoolStudentProfile::create([
                'user_id' => $user->id,
                'school_id' => $request->school_id,
                'active_class_model_id' => $request->active_class_model_id,
                'birth_date' => $request->birth_date,
                'student_number' => $request->student_number,
                'tc_no' => $request->tc_no,
                'gender' => $request->gender,
                'address' => $request->address,
                'parent_name' => $request->parent_name,
                'parent_phone' => $request->parent_phone,
                'registered_at' => now(),
            ]);

            StudentParent::create([
                'school_student_profile_id' => $profile->id,
                'type' => 'anne',
                'name' => $request->parent_name ?? 'Belirtilmedi',
                'phone' => $request->parent_phone,
            ]);

            StudentHealthProfile::create([
                'school_student_profile_id' => $profile->id,
                'blood_type' => 'bilinmiyor',
                'health_insurance' => 'diğer'
            ]);
        });

        return response()->json(['message' => 'Öğrenci profili başarıyla tamamlandı.'], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/students/{id}",
     *     summary="Öğrenci detayını getirir",
     *     tags={"SchoolStudentProfile"},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Başarılı")
     * )
     */
    public function show($school, $id)
    {
        $student = User::with('schoolStudentProfile')->where('id', $id)->firstOrFail();
        $this->authorize('view', $student);

        return response()->json($student);
    }

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/students/byclass/{classModel}",
     *     summary="Belirli bir sınıfa ait öğrencileri listeler",
     *     tags={"SchoolStudentProfile"},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="classModel", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Başarılı")
     * )
     */
    public function getByClassModel($school, $classModel)
    {
        $students = User::where('role', 'schoolstudent')
            ->whereHas('schoolStudentProfile', function ($q) use ($classModel, $school) {
                $q->where('active_class_model_id', $classModel)
                    ->where('school_id', $school);
            })
            ->with('schoolStudentProfile')
            ->get();

        return response()->json($students);
    }

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/students",
     *     summary="Okuldaki tüm öğrencileri listeler",
     *     tags={"SchoolStudentProfile"},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Başarılı")
     * )
     */
    public function index($school)
    {
        $students = User::where('role', 'schoolstudent')
            ->whereHas('schoolStudentProfile', function ($q) use ($school) {
                $q->where('school_id', $school);
            })
            ->with('schoolStudentProfile')
            ->get();

        return response()->json($students);
    }

    /**
     * @OA\Put(
     *     path="/api/schools/{school}/students/{id}/update",
     *     summary="Öğrenciyi günceller (hem user hem profile)",
     *     tags={"SchoolStudentProfile"},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateStudentRequest")),
     *     @OA\Response(response=200, description="Öğrenci başarıyla güncellendi"),
     *     @OA\Response(response=403, description="Yetkisiz işlem"),
     *     @OA\Response(response=404, description="Öğrenci bulunamadı")
     * )
     */
    public function update(UpdateStudentRequest $request, $school, $id)
    {
        $student = User::with(['schoolStudentProfile', 'schoolStudentProfile.studentHealthProfile'])
            ->where('role', 'schoolstudent')
            ->where('id', $id)
            ->firstOrFail();

        $this->authorize('update', $student);

        $data = $request->validated();

        DB::transaction(function () use ($student, $data) {
            // User güncelleme
            $student->update([
                'name' => $data['name'] ?? $student->name,
                'email' => $data['email'] ?? $student->email,
                'userName' => $data['userName'] ?? $student->userName,
                'password' => isset($data['password']) ? bcrypt($data['password']) : $student->password,
            ]);

            // Profile güncelleme
            if ($student->schoolStudentProfile) {
                $student->schoolStudentProfile->update([
                    'phone' => $data['phone'] ?? $student->schoolStudentProfile->phone,
                    'address' => $data['address'] ?? $student->schoolStudentProfile->address,
                    'parent_name' => $data['parent_name'] ?? $student->schoolStudentProfile->parent_name,
                    'parent_phone' => $data['parent_phone'] ?? $student->schoolStudentProfile->parent_phone,
                ]);
            }

            // Health profile güncelleme
            if ($student->schoolStudentProfile && $student->schoolStudentProfile->studentHealthProfile) {
                $student->schoolStudentProfile->studentHealthProfile->update([
                    'blood_type' => $data['blood_type'] ?? $student->schoolStudentProfile->studentHealthProfile->blood_type,
                    'health_insurance' => $data['health_insurance'] ?? $student->schoolStudentProfile->studentHealthProfile->health_insurance,
                ]);
            }
        });

        return response()->json([
            'message' => 'Öğrenci bilgileri başarıyla güncellendi.',
            'student' => $student->fresh(['schoolStudentProfile', 'schoolStudentProfile.studentHealthProfile'])
        ]);
    }
    /**
     * @OA\Delete(
     *     path="/api/schools/{school}/students/{id}",
     *     summary="Öğrenciyi sistemden siler (User + ilişkili tüm profiller)",
     *     tags={"SchoolStudentProfile"},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/DeleteStudentRequest")),
     *     @OA\Response(response=200, description="Öğrenci başarıyla silindi"),
     *     @OA\Response(response=403, description="Yetkisiz işlem"),
     *     @OA\Response(response=404, description="Öğrenci bulunamadı")
     * )
     */
    public function destroy(DeleteStudentRequest $request, $school, $id)
    {
        $student = User::where('role', 'schoolstudent')
            ->with('schoolStudentProfile')
            ->whereHas('schoolStudentProfile', function ($q) use ($school) {
                $q->where('school_id', $school);
            })
            ->findOrFail($id);

        $this->authorize('delete', $student);

        if (!$request->input('confirm')) {
            return response()->json(['message' => 'Silme işlemi onaylanmadı.'], 400);
        }

        $student->delete();

        return response()->json(['message' => 'Öğrenci ve tüm ilişkili verileri başarıyla silindi.']);
    }
}
