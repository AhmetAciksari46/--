<?php

namespace App\Http\Controllers\School\General;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\School;
use App\Traits\ApiResponser;
use App\Http\Requests\Manager\UpdateSchoolRequest;
use App\Http\Requests\Manager\CreateSchoolbyAdminRequest;
use App\Http\Requests\Manager\UpdateSchoolbyAdminRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\ManagerProfile;
use App\Models\TeacherProfile;
use App\Models\SchoolStudentProfile;
use App\Models\User;

/**
 * @OA\Tag(
 *     name="Genel - Schools",
 *     description="Okul yönetimi işlemleri (Manager, Teacher, Student, Admin)",
 * )
 */
class SchoolController extends Controller
{
    use ApiResponser;
    /**
     * @OA\Get(
     *     path="/api/school/info",
     *     tags={"Genel - Schools"},
     *     summary="Kullanıcının bağlı olduğu okul bilgisini getirir",
     *     description="Manager, teacher veya student rollü kullanıcının okul bilgisini döner.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Okul bilgisi başarıyla getirildi"),
     *     @OA\Response(response=404, description="Okul bilgisi bulunamadı")
     * )
     */
    public function info(Request $request)
    {
        if (!auth()->user()->can('school.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }
        $user = Auth::user(); // Giriş yapmış kullanıcı

        if ($user->hasRole('manager')) {

            $profile = ManagerProfile::where('user_id', $user->id)->first();
            $schoolId = $profile?->school_id;
        } else if ($user->hasRole('teacher')) {
            $profile = TeacherProfile::where('user_id', $user->id)->first();
            $schoolId = $profile?->school_id;
        } else if ($user->hasRole('schoolstudent')) {
            $profile = SchoolStudentProfile::where('user_id', $user->id)->first();
            $schoolId = $profile?->school_id;
        }


        // School kontrolü
        if ($schoolId) {
            try {
                $school = School::findOrFail($schoolId);
                return $this->successResponse($school, 'Okul bilgisi başarıyla getirildi', 200);
            } catch (\Exception $e) {
                return $this->errorResponse('Okul bilgisi alınırken bir hata oluştu: ' . $e->getMessage(), 500);
            }
        } else {
            return $this->errorResponse('Okul bilgisi hatalı.', 404);
        }
    }


    /**
     * @OA\Post(
     *     path="/api/admin/school/createschool",
     *     tags={"Genel - Schools"},
     *     summary="Yeni okul oluştur (admin rolü)",
     *     description="Admin kullanıcılar için yeni okul oluşturma işlemi.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "nickname"},
     *             @OA\Property(property="name", type="string", example="Açıksarı Koleji"),
     *             @OA\Property(property="nickname", type="string", example="ackolej"),
     *             @OA\Property(property="address", type="string", example="Kahramanmaraş"),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="img_path", type="string", example="uploads/schools/logo.png"),
     *             @OA\Property(property="manager_id", type="integer", example="1")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Okul başarıyla oluşturuldu"),
     *     @OA\Response(response=403, description="Manager zaten bir okula sahip"),
     *     @OA\Response(response=500, description="Sunucu hatası")
     * )
     */

    // Admin için okul oluşturma
    public function createSchool(CreateSchoolbyAdminRequest $request)
    {
        if (!auth()->user()->can('school.create')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }
        try {
            $school = School::create([
                'name' => $request->name,
                'nickname' => $request->nickname,
                'address' => $request->address,
                'is_active' => $request->input('is_active', true),
                'img_path' => $request->img_path,
                'manager_id' => $request->manager_id, // admin belirleyecek
            ]);
            // 2. Manager'ın profilini güncelle (KRİTİK KISIM)
            $manager = User::find($request->manager_id);

            if ($manager && $manager->managerProfile) {
                $manager->managerProfile->update([
                    'school_id' => $school->id
                ]);
            }
            return $this->successResponse($school->fresh(), 'Okul başarıyla oluşturuldu.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Okul oluşturulurken bir hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/admin/school/update/{school}",
     *     tags={"Genel - Schools"},
     *     summary="Okul güncelle (Admin)",
     *     description="Admin kullanıcı okul bilgilerini günceller.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         description="Okul ID",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateSchoolbyAdminRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Okul başarılı şekilde güncellendi"
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Yetkiniz yok"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Sunucu hatası"
     *     )
     * )
     */
    public function updateSchool(UpdateSchoolbyAdminRequest $request, School $school)
    {
        // Okul yoksa
        if (!auth()->user()->can('school.update')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }
        try {
            $school->update($request->validated());
            return $this->successResponse($school->fresh(), 'Okul başarıyla güncellendi.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Okul güncellenirken bir hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/school/getschools",
     *     tags={"Genel - Schools"},
     *     summary="Tüm okulları listele (Admin)",
     *     description="Sadece school.view iznine sahip adminler listeyi görebilir.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Okul listesi başarıyla getirildi"),
     *     @OA\Response(response=403, description="Yetkiniz yok")
     * )
     */

    // Admin için okul listesi
    public function schoollist()
    {
        if (!auth()->user()->can('school.wiew.list')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }
        try {
            $schools = School::with('manager')->get();
            return $this->successResponse($schools, 'Okul listesi başarıyla getirildi', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Okul listesi alınırken bir hata oluştu: ' . $e->getMessage(), 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/api/admin/school/getschoolbyid/{id}",
     *     tags={"Genel - Schools"},
     *     summary="Tek okul bilgisi getir (Admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Okul bilgisi başarıyla getirildi"),
     *     @OA\Response(response=403, description="Yetkiniz yok"),
     *     @OA\Response(response=404, description="Okul bulunamadı")
     * )
     */

    // Admin için tek okul getirme
    public function getSchool(School $school)
    {
        if (!auth()->user()->can('school.view.detail')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        try {
            $info =  School::with('manager')->findOrFail($school->id);
            return $this->successResponse($info, 'Okul bilgisi başarıyla getirildi', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Okul bilgisi alınırken bir hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/schools/{id}",
     *     tags={"Genel - Schools"},
     *     summary="Okul sil (Admin)",
     *     description="Sadece school.delete iznine sahip adminler okul silebilir.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Okul başarıyla silindi"),
     *     @OA\Response(response=403, description="Yetkiniz yok")
     * )
     */

    // Admin için okul silme
    public function deleteschool(School $school)
    {
        if (!Auth::user()->can('school.delete')) {
            return response()->json([
                'message' => 'Bu işlemi yapma yetkiniz yok (school.delete izni gerekli).'
            ], 403);
        }
        try {
            $school->delete();
            return $this->successResponse(null, 'Okul başarıyla silindi', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Okul silinirken bir hata oluştu: ' . $e->getMessage(), 500);
        }
    }
}
