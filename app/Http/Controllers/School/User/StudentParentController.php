<?php

namespace App\Http\Controllers\School\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Models\SchoolStudentProfile;
use App\Models\StudentParent;
use App\Models\School;
use App\Http\Requests\StudentParent\StoreStudentParentRequest;
use App\Http\Requests\StudentParent\UpdateStudentParentRequest;

/**
 * @OA\Tag(
 *     name="Manager & Teacher - Student Veli İşlemleri",
 *     description="Öğrenci velileri için CRUD işlemleri"
 * )
 */
class StudentParentController extends Controller
{
    use  ApiResponser;

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/students/{profile}/parents",
     *     summary="Belirli öğrencinin velilerini listeler",
     *     tags={"Manager & Teacher - Student Veli İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="profile", description="schoolstudentprofile idsi", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Parent list"),
     * )
     */

    public function index(School $school, SchoolStudentProfile $profile)
    {

        if ($profile->school_id !== $school->id) {
            abort(403, 'Bu öğrenci bu okula ait değil.');
        }
        if (!auth()->user()->can('student.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }



        $parents = $profile->parents;
        if ($parents->isEmpty()) {
            return $this->successResponse(
                [],
                "Bu öğrenciye ait veli bulunmamaktadır.",
                200
            );
        }
        return $this->successResponse(
            $parents,
            "Veliler başarıyla listelendi.",
            200
        );
    }

    /**
     * @OA\Post(
     *     path="/api/schools/{school}/students/{profile}/parents",
     *     summary="Öğrenciye yeni veli ekler",
     *     tags={"Manager & Teacher - Student Veli İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="profile", description="schoolstudentprofile idsi", in="path", required=true, @OA\Schema(type="integer")),
     * 
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StoreStudentParentRequest")),
     *
     *     @OA\Response(response=201, description="Parent created")
     * )
     */
    public function store(StoreStudentParentRequest $request, School $school, SchoolStudentProfile $profile)
    {
        if ($profile->school_id !== $school->id) {
            abort(403, 'Bu öğrenci bu okula ait değil.');
        }
        if (!auth()->user()->can('student.create')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }
        $parent = $profile->parents()->create($request->validated());

        return $this->successResponse($parent, "Veli başarıyla oluşturuldu.", 201);
    }

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/students/{profile}/parents/{parent}",
     *     summary="Öğrencinin belirli velisini getirir",
     *     tags={"Manager & Teacher - Student Veli İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="profile", description="studentprofile id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="parent", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Parent details")
     * )
     */
    public function show(School $school, SchoolStudentProfile $profile, StudentParent $parent)
    {
        if (!auth()->user()->can('student.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        if ($parent->school_student_profile_id !== $profile->id) {
            return $this->errorResponse("Bu veli bu öğrenciye ait değil.", 403);
        }

        return $this->successResponse($parent, "Veli bilgisi getirildi.", 200);
    }

    /**
     * @OA\Put(
     *     path="/api/schools/{school}/students/{profile}/parents/{parent}",
     *     summary="Veli bilgilerini günceller",
     *     tags={"Manager & Teacher - Student Veli İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="profile", description="studentprofile id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="parent", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateStudentParentRequest")),
     *
     *     @OA\Response(response=200, description="Parent updated")
     * )
     */
    public function update(UpdateStudentParentRequest $request, School $school, SchoolStudentProfile $profile, StudentParent $parent)
    {
        if (!auth()->user()->can('student.update')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }
        if ($profile->school_id !== $school->id) {
            abort(403, 'Bu öğrenci bu okula ait değil.');
        }
        if ($parent->school_student_profile_id !== $profile->id) {
            return $this->errorResponse("Bu veli bu öğrenciye ait değil.", 403);
        }

        $parent->update($request->validated());

        return $this->successResponse($parent, "Veli bilgisi güncellendi.", 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/schools/{school}/students/{profile}/parents/{parent}",
     *     summary="Öğrenciden veliyi siler",
     *     tags={"Manager & Teacher - Student Veli İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(response=200, description="Parent deleted")
     * )
     */
    public function destroy(School $school, SchoolStudentProfile $profile, StudentParent $parent)
    {
        if (!auth()->user()->can('student.delete')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }
        if ($profile->school_id !== $school->id) {
            abort(403, 'Bu öğrenci bu okula ait değil.');
        }
        if ($parent->school_student_profile_id !== $profile->id) {
            return $this->errorResponse("Bu veli bu öğrenciye ait değil.", 403);
        }

        $parent->delete();

        return $this->successResponse(null, "Veli silindi.", 200);
    }
}
