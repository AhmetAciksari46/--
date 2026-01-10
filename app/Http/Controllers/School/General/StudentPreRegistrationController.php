<?php

namespace App\Http\Controllers\School\General;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\School;
use App\Models\StudentPreRegistration;
use App\Models\SchoolHasGrade;

use App\Traits\ApiResponser;
use App\Http\Resources\StudentPreRegistrationResource;

use App\Http\Requests\StoreStudentPreRegistrationRequest;
use App\Http\Requests\UpdateStudentPreRegistrationRequest;

use App\Enums\ParentsStatus;
use App\Enums\PreRegistrationStatus;

/**
 * @OA\Tag(
 *     name="Manager & Teacher StudentPreRegistration İşlemleri",
 *     description="Okul öğrenci ön kayıt yönetimi"
 * )
 */
class StudentPreRegistrationController extends Controller
{
    use ApiResponser;

    // ----------------------------------------------------------------------
    // School erişim kontrolü (ClassModelController ile aynı mantık)
    private function authorizeSchoolAccess(School $school)
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('manager')) {
            if ($user->managerProfile && $user->managerProfile->school_id == $school->id) {
                return true;
            }
            abort(403, 'Bu işlem için yetkiniz yok. (Manager Okul Erişim Engeli)');
        }

        if ($user->hasRole('teacher')) {
            if ($user->teacherProfile && $user->teacherProfile->school_id == $school->id) {
                return true;
            }
            abort(403, 'Bu işlem için yetkiniz yok. (Teacher Okul Erişim Engeli)');
        }

        abort(403, 'Bu işlem için yetkiniz yok.');
    }

    // ----------------------------------------------------------------------


    /**
     * @OA\Get(
     *     path="/api/schools/{school}/student-pre-registrations",
     *     summary="Okuldaki tüm ön kayıtları getirir",
     *     tags={"Manager & Teacher StudentPreRegistration İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         description="School ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(response=200, description="Ön kayıtlar listelendi."),
     *     @OA\Response(response=403, description="Yetkiniz yok."),
     *     @OA\Response(response=500, description="Sunucu hatası.")
     * )
     */
    public function index(School $school)
    {
        $this->authorizeSchoolAccess($school);

        if (!auth()->user()->can('student.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        try {
            $items = StudentPreRegistration::query()
                ->where('school_id', $school->id) // ✅ sadece schoolId filtresi
                ->with([
                    'grade:id,name',
                    'school:id,name'
                ])
                ->latest()
                ->get();

            return $this->successResponse(
                StudentPreRegistrationResource::collection($items),
                "Ön kayıtlar başarıyla listelendi.",
                200
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Liste alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/schools/{school}/student-pre-registrations",
     *     summary="Yeni ön kayıt oluşturur",
     *     tags={"Manager & Teacher StudentPreRegistration İşlemleri"},
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
     *         @OA\JsonContent(ref="#/components/schemas/StoreStudentPreRegistrationRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Ön kayıt başarıyla oluşturuldu."
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Bu okul belirtilen grade seviyesine sahip değildir."
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Yetki hatası"
     *     )
     * )
     */
    public function store(StoreStudentPreRegistrationRequest $request, School $school)
    {

        $this->authorizeSchoolAccess($school);

        if (!auth()->user()->can('student.create')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        // ✅ Grade bu okula ait mi?
        $allowed = SchoolHasGrade::where('school_id', $school->id)
            ->where('grade_id', $request->grade_id)
            ->exists();

        if (!$allowed) {
            return $this->errorResponse(
                "Bu okul belirtilen grade seviyesine sahip değildir.",
                422
            );
        }

        try {
            $data = $request->validated();

            // ✅ school_id backend set edilir
            $data['school_id'] = $school->id;

            $item = StudentPreRegistration::create($data);
            $item->load([
                'grade:id,name',
                'school:id,name'
            ]);

            return $this->successResponse(
                new StudentPreRegistrationResource($item),
                "Ön kayıt başarıyla oluşturuldu.",
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Ön kayıt oluşturulurken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/student-pre-registrations/{studentPreRegistration}",
     *     summary="Belirli bir ön kaydı getirir",
     *     tags={"Manager & Teacher StudentPreRegistration İşlemleri"},
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
     *         name="studentPreRegistration",
     *         in="path",
     *         required=true,
     *         description="Ön kayıt ID",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\Response(response=200, description="Ön kayıt detayları getirildi."),
     *     @OA\Response(response=403, description="Bu işlemi yapmak için yetkiniz yok.")
     * )
     */
    public function show(School $school, StudentPreRegistration $studentPreRegistration)
    {
        $this->authorizeSchoolAccess($school);

        if (!auth()->user()->can('student.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        if ($studentPreRegistration->school_id !== $school->id) {
            return $this->errorResponse("Bu ön kayıt bu okula ait değil.", 403);
        }

        try {
            $studentPreRegistration->load([
                'grade:id,name',
                'school:id,name'
            ]);

            return $this->successResponse(
                new StudentPreRegistrationResource($studentPreRegistration),
                "Ön kayıt bilgileri getirildi.",
                200
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Ön kayıt bilgisi alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/schools/{school}/student-pre-registrations/{studentPreRegistration}",
     *     summary="Ön kaydı günceller",
     *     tags={"Manager & Teacher StudentPreRegistration İşlemleri"},
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
     *         name="studentPreRegistration",
     *         in="path",
     *         required=true,
     *         description="Ön kayıt ID",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateStudentPreRegistrationRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Ön kayıt güncellendi."),
     *     @OA\Response(response=422, description="Bu okul belirtilen grade seviyesine sahip değildir.")
     * )
     */
    public function update(UpdateStudentPreRegistrationRequest $request, School $school, StudentPreRegistration $studentPreRegistration)
    {
        $this->authorizeSchoolAccess($school);

        if (!auth()->user()->can('student.update')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        if ($studentPreRegistration->school_id !== $school->id) {
            return $this->errorResponse("Bu ön kayıt bu okula ait değil.", 403);
        }

        // ✅ grade değişiyorsa grade okulda var mı kontrol et
        if ($request->filled('grade_id')) {
            $allowed = SchoolHasGrade::where('school_id', $school->id)
                ->where('grade_id', $request->grade_id)
                ->exists();

            if (!$allowed) {
                return $this->errorResponse(
                    "Bu okul belirtilen grade seviyesine sahip değildir.",
                    422
                );
            }
        }

        try {
            $data = $request->validated();

            // ✅ school_id asla değiştirilemez
            unset($data['school_id']);

            $studentPreRegistration->update($data);
            $studentPreRegistration->load([
                'grade:id,name',
                'school:id,name'
            ]);

            return $this->successResponse(
                new StudentPreRegistrationResource($studentPreRegistration),
                "Ön kayıt başarıyla güncellendi.",
                200
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Ön kayıt güncellenirken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/schools/{school}/student-pre-registrations/{studentPreRegistration}",
     *     summary="Ön kaydı siler",
     *     tags={"Manager & Teacher StudentPreRegistration İşlemleri"},
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
     *         name="studentPreRegistration",
     *         in="path",
     *         required=true,
     *         description="Ön kayıt ID",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\Response(response=200, description="Ön kayıt silindi.")
     * )
     */
    public function destroy(School $school, StudentPreRegistration $studentPreRegistration)
    {
        $this->authorizeSchoolAccess($school);

        if (!auth()->user()->can('student.delete')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        if ($studentPreRegistration->school_id !== $school->id) {
            return $this->errorResponse("Bu ön kayıt bu okula ait değil.", 403);
        }

        try {
            $studentPreRegistration->delete();

            return $this->successResponse(
                null,
                "Ön kayıt başarıyla silindi.",
                200
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Ön kayıt silinirken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/student-pre-registrations/options",
     *     summary="Ön kayıt dropdown seçenekleri",
     *     tags={"Manager & Teacher StudentPreRegistration İşlemleri"},
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
     *     @OA\Response(response=200, description="Seçenekler başarıyla getirildi.")
     * )
     */
    public function options(School $school)
    {
        $this->authorizeSchoolAccess($school);

        if (!auth()->user()->can('student.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        try {
            return $this->successResponse([
                'parents_status' => [
                    ['value' => null, 'label' => '-'],
                    ...ParentsStatus::options(),
                ],
                'status' => PreRegistrationStatus::options(),
            ], "Seçenekler başarıyla getirildi.", 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Seçenekler alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }
}
