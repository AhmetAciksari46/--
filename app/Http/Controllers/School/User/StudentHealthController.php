<?php

namespace App\Http\Controllers\School\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentHealthProfile;
use App\Http\Requests\Student\Health\StudentHealthRequest;
use App\Traits\ApiResponser;
use App\Models\StudentHealth;
use App\Models\SchoolStudentProfile;
use App\Models\School;

/**
 * @OA\Tag(
 *     name="Manager & Teacher Student Health",
 *     description="Öğrenci sağlık bilgileri işlemleri"
 * )
 */
class StudentHealthController extends Controller
{
    use ApiResponser;
    /**
     * @OA\Get(
     *   path="/api/schools/{school}/students/{profile}/health",
     *   summary="Öğrenci sağlık bilgisi görüntüleme",
     *   tags={"Manager & Teacher Student Health"},
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\Parameter(name="school", in="path", required=true, example=1),
     *   @OA\Parameter(name="profile",description="schoolstudentprofile idsi", in="path", required=true, example=22),
     *
     *   @OA\Response(
     *      response=200,
     *      description="Başarılı",
     *      @OA\JsonContent(type="object")
     *   ),
     *   @OA\Response(response=404, description="Bulunamadı")
     * )
     */
    public function show(School $school, SchoolStudentProfile $profile)
    {
        if (!auth()->user()->can('student.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        if ($profile->school_id !== $school->id) {
            abort(403, 'Bu öğrenci bu okula ait değil.');
        }
        if (!auth()->user()->can('student.view')) {
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }

        $health = StudentHealthProfile::where('school_student_profile_id', $profile->id)->first();

        if (!$health) {
            return $this->errorResponse('Sağlık kaydı bulunamadı.', 404);
        }

        return $this->successResponse($health, "Veriler başarıyla getirildi.", 200);
    }

    /**
     * @OA\Post(
     *   path="/api/schools/{school}/students/{profile}/health",
     *   summary="Öğrenci sağlık kaydı oluşturma",
     *   tags={"Manager & Teacher Student Health"},
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\Parameter(name="school", in="path", required=true),
     *   @OA\Parameter(name="profile",description="schoolstudentprofile idsi", in="path", required=true),
     *
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/StudentHealthRequest")
     *   ),
     *
     *   @OA\Response(response=201, description="Oluşturuldu"),
     *   @OA\Response(response=422, description="Validasyon hatası")
     * )
     */
    public function store(StudentHealthRequest $request, School $school, SchoolStudentProfile $profile)
    {
        if (!auth()->user()->can('student.create')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        if ($profile->school_id !== $school->id) {
            abort(403, 'Bu öğrenci bu okula ait değil.');
        }
        if (!auth()->user()->can('student.create')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        $data = $request->validated();
        $data['school_student_profile_id'] = $profile->id;

        $health = StudentHealthProfile::create($data);

        return $this->successResponse($health, 'Sağlık bilgisi oluşturuldu.', 201);
    }

    /**
     * @OA\Put(
     *   path="/api/schools/{school}/students/{profile}/health",
     *   summary="Öğrenci sağlık kaydı güncelleme",
     *   tags={"Manager & Teacher Student Health"},
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\Parameter(name="school", in="path"),
     *   @OA\Parameter(name="profile",description="schoolstudentprofile idsi", in="path"),
     *
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/StudentHealthRequest")
     *   ),
     *
     *   @OA\Response(response=200, description="Güncellendi"),
     *   @OA\Response(response=404, description="Bulunamadı")
     * )
     */
    public function update(StudentHealthRequest $request, School $school, SchoolStudentProfile $profile)
    {
        if ($profile->school_id !== $school->id) {
            abort(403, 'Bu öğrenci bu okula ait değil.');
        }
        if (!auth()->user()->can('student.update')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        $health = StudentHealthProfile::where('school_student_profile_id', $profile->id)->first();

        if (!$health) {
            return $this->errorResponse('Sağlık kaydı bulunamadı.', 404);
        }

        $health->update($request->validated());

        return $this->successResponse($health, 'Sağlık bilgisi güncellendi.', 200);
    }

    /**
     * @OA\Delete(
     *   path="/api/schools/{school}/students/{profile}/health",
     *   summary="Öğrenci sağlık kaydı silme",
     *   tags={"Manager & Teacher Student Health"},
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\Parameter(name="school", in="path"),
     *   @OA\Parameter(name="profile",description="schoolstudentprofile idsi", in="path"),
     *
     *   @OA\Response(response=200, description="Silindi"),
     *   @OA\Response(response=404, description="Bulunamadı")
     * )
     */
    public function destroy(School $school, SchoolStudentProfile $profile)
    {
        if ($profile->school_id !== $school->id) {
            abort(403, 'Bu öğrenci bu okula ait değil.');
        }
        if (!auth()->user()->can('student.delete')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        $health = StudentHealthProfile::where('school_student_profile_id', $profile->id)->first();

        if (!$health) {
            return $this->errorResponse('Sağlık kaydı bulunamadı.', 404);
        }

        $health->delete();

        return $this->successResponse([], 'Sağlık bilgisi silindi.', 200);
    }
}
