<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Traits\ApiResponser;
use App\Http\Requests\Manager\UpdateSchoolRequest;
use App\Http\Requests\Manager\CreateSchoolRequest;
use App\Http\Requests\Manager\CreateSchoolbyAdminRequest;
use App\Http\Requests\Manager\UpdateSchoolbyAdminRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\ManagerProfile;
use App\Models\TeacherProfile;
use App\Models\SchoolStudentProfile;

/**
 * @OA\Tag(
 *     name="Schools",
 *     description="Okul yönetimi işlemleri (Manager, Teacher, Student, Admin)",
 * )
 * @OAS\SecurityScheme(
 *      securityScheme="bearer_token",
 *      type="http",
 *      scheme="bearer"
 * )
 */
class SchoolController extends Controller
{
    use ApiResponser;
    /**
     * @OA\Get(
     *     path="/api/schools/info",
     *     tags={"Schools"},
     *     summary="Kullanıcının bağlı olduğu okul bilgisini getirir",
     *     description="Manager, teacher veya student rollü kullanıcının okul bilgisini döner.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Okul bilgisi başarıyla getirildi"),
     *     @OA\Response(response=404, description="Okul bilgisi bulunamadı")
     * )
     */
    public function info(Request $request)
    {
        $user = Auth::user(); // Giriş yapmış kullanıcı

        if ($user->hasRole('manager')) {

            $profile = ManagerProfile::where('user_id', $user->id)->first();
            $schoolId = $profile?->schoolId;
        } else if ($user->hasRole('teacher')) {
            $profile = TeacherProfile::where('user_id', $user->id)->first();
            $schoolId = $profile?->school_id;
        } else if ($user->hasRole('schoolstudent')) {
            $profile = SchoolStudentProfile::where('user_id', $user->id)->first();
            $schoolId = $profile?->schoolId;
        }


        // School kontrolü
        if ($schoolId) {
            $school = School::find($schoolId);

            if ($school) {
                return $this->successResponse($school, 'Okul bilgisi başarıyla getirildi', 200);
            } else {
                return $this->errorResponse('Okul bilgisi bulunamadı.', 404);
            }
        }



        // $profile = ManagerProfile::where('user_id', $user->id)
        //     ->with('user')
        //     ->first();
        // $school =  School::findOrFail($profile->schoolId);
        // if ($school) {

        //     return $this->successResponse($school, 'Okul bilgisi başarıyla getirildi', 200);
        // } else {
        //     return $this->errorResponse('Okul bilgisi bulunamadı.', 404);
        // }
    }
    /**
     * @OA\Get(
     *     path="/api/schools",
     *     tags={"Schools"},
     *     summary="Okul bilgisi (aktif oturumdan alınır)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Okul bilgisi başarıyla getirildi"),
     *     @OA\Response(response=404, description="Okul bulunamadı")
     * )
     */
    public function index(Request $request)
    {
        return $this->successResponse($request);

        $school = $request->attributes->get('school');
        if (!$school) {
            return $this->errorResponse('Okul bilgisi bulunamadı.', 404);
        }
        return $this->successResponse($school);
    }
    /**
     * @OA\Post(
     *     path="/api/schools/create",
     *     tags={"Schools"},
     *     summary="Yeni okul oluştur (Manager rolü)",
     *     description="Manager kullanıcılar için yeni okul oluşturma işlemi. Eğer mevcut okulu varsa hata döner.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "nickname"},
     *             @OA\Property(property="name", type="string", example="Açıksarı Koleji"),
     *             @OA\Property(property="nickname", type="string", example="ackolej"),
     *             @OA\Property(property="address", type="string", example="Kahramanmaraş"),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="img_path", type="string", example="uploads/schools/logo.png")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Okul başarıyla oluşturuldu"),
     *     @OA\Response(response=403, description="Manager zaten bir okula sahip"),
     *     @OA\Response(response=500, description="Sunucu hatası")
     * )
     */
    public function create(CreateSchoolRequest $request)
    {
        $user = $request->user();

        // Manager profilini al
        $managerProfile = $user->managerProfile;
        if ($managerProfile->schoolId) {
            return $this->errorResponse('Mevcut okulunuz var, yeni okul oluşturamazsınız.', 403);
        }
        try {
            $school = School::create([
                'name' => $request->name,
                'nickname' => $request->nickname,
                'address' => $request->address,
                'is_active' => $request->input('is_active', true),
                'img_path' => $request->img_path,
                'manager_id' => $request->user()->id, // otomatik atanıyor
            ]);

            $managerProfile->update(['schoolId' => $school->id]);

            return $this->successResponse($school->fresh(), 'Okul başarıyla oluşturuldu.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Okul oluşturulurken bir hata oluştu: ' . $e->getMessage(), 500);
        }
    }
    /**
     * @OA\Put(
     *     path="/api/schools/{id}/update",
     *     tags={"Schools"},
     *     summary="Okul bilgilerini güncelle (Manager rolü)",
     *     description="Manager kullanıcı, bağlı olduğu okulun bilgilerini günceller.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Açıksarı Anadolu Lisesi"),
     *             @OA\Property(property="nickname", type="string", example="ackolej"),
     *             @OA\Property(property="address", type="string", example="Kahramanmaraş Onikişubat"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Okul başarıyla güncellendi"),
     *     @OA\Response(response=403, description="Manager bir okula bağlı değil"),
     *     @OA\Response(response=500, description="Sunucu hatası")
     * )
     */
    public function update(UpdateSchoolRequest $request, School $school)
    {
        $user = $request->user();

        // Manager profilini al  
        $managerProfile = $user->managerProfile;
        if (!$managerProfile->schoolId) {
            return $this->errorResponse('Mevcut okulunuz yok, yeni okul oluştur.', 403);
        }

        try {
            $school->update($request->validated());
            return $this->successResponse($school->fresh(), 'Okul başarıyla güncellendi.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Okul güncellenirken bir hata oluştu: ' . $e->getMessage(), 500);
        }
    }
    /**
     * @OA\Post(
     *     path="/api/admin/schools/create",
     *     tags={"Admin - Schools"},
     *     summary="Admin tarafından okul oluştur",
     *     description="Admin kullanıcılar, istediği manager_id’ye bağlı yeni okul oluşturabilir.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"name", "nickname", "manager_id"},
     *             @OA\Property(property="name", type="string", example="Ede Koleji"),
     *             @OA\Property(property="nickname", type="string", example="edekolej"),
     *             @OA\Property(property="manager_id", type="integer", example=2),
     *             @OA\Property(property="address", type="string", example="Maraş Dulkadiroğlu"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Okul başarıyla oluşturuldu"),
     *     @OA\Response(response=500, description="Sunucu hatası")
     * )
     */

    // Admin için okul oluşturma
    public function createSchool(CreateSchoolbyAdminRequest $request)
    {
        try {
            $school = School::create([
                'name' => $request->name,
                'nickname' => $request->nickname,
                'address' => $request->address,
                'is_active' => $request->input('is_active', true),
                'img_path' => $request->img_path,
                'manager_id' => $request->manager_id, // admin belirleyecek
            ]);
            return $this->successResponse($school->fresh(), 'Okul başarıyla oluşturuldu.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Okul oluşturulurken bir hata oluştu: ' . $e->getMessage(), 500);
        }
    }
    /**
     * @OA\Put(
     *     path="/api/admin/schools/{id}/update",
     *     tags={"Admin - Schools"},
     *     summary="Admin tarafından okul bilgilerini güncelle",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="name", type="string", example="Ede Koleji Güncel"),
     *         @OA\Property(property="is_active", type="boolean", example=true)
     *     )),
     *     @OA\Response(response=201, description="Okul başarıyla güncellendi"),
     *     @OA\Response(response=500, description="Sunucu hatası")
     * )
     */

    // Admin için okul güncelleme
    public function updateSchool(UpdateSchoolbyAdminRequest $request, School $school)
    {
        // Okul yoksa

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
     *     tags={"Admin - Schools"},
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

        if (!Auth::user()->can('school.view')) {
            return response()->json([
                'message' => 'Bu işlemi yapma yetkiniz yok (school.delete izni gerekli).'
            ], 403);
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
     *     tags={"Admin - Schools"},
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
        if (!Auth::user()->can('school.view')) {
            return response()->json([
                'message' => 'Bu işlemi yapma yetkiniz yok (school.delete izni gerekli).'
            ], 403);
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
     *     tags={"Admin - Schools"},
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
