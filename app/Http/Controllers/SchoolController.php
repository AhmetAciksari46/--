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


class SchoolController extends Controller
{
    use ApiResponser;

    public function info(Request $request)
    {
        $user = Auth::user(); // Giriş yapmış kullanıcı
        $profile = ManagerProfile::where('user_id', $user->id)
            ->with('user')
            ->first();
        $school =  School::findOrFail($profile->schoolId);
        if ($school) {

            return $this->successResponse($school, 'Okul bilgisi başarıyla getirildi', 200);
        } else {
            return $this->errorResponse('Okul bilgisi bulunamadı.', 404);
        }
    }

    public function index(Request $request)
    {
        return $this->successResponse($request);

        $school = $request->attributes->get('school');
        if (!$school) {
            return $this->errorResponse('Okul bilgisi bulunamadı.', 404);
        }
        return $this->successResponse($school);
    }
    public function create(CreateSchoolRequest $request)
    {
        try {
            $school = School::create([
                'name' => $request->name,
                'nickname' => $request->nickname,
                'address' => $request->address,
                'is_active' => $request->input('is_active', true),
                'img_path' => $request->img_path,
                'manager_id' => $request->user()->id, // otomatik atanıyor
            ]);
            return $this->successResponse($school->fresh(), 'Okul başarıyla oluşturuldu.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Okul oluşturulurken bir hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    public function update(UpdateSchoolRequest $request, School $school)
    {
        try {
            $school->update($request->validated());
            return $this->successResponse($school->fresh(), 'Okul başarıyla güncellendi.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Okul güncellenirken bir hata oluştu: ' . $e->getMessage(), 500);
        }
    }
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
