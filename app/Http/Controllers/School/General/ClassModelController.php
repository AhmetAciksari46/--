<?php

namespace App\Http\Controllers\School\General;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\ClassModel;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\SchoolHasGrade;
use App\Http\Requests\Class\StoreClassRequest;
use App\Http\Requests\Class\UpdateClassRequest;
use App\Models\School;
use App\Traits\ApiResponser;

/**
 * @OA\Tag(
 *     name="Manager & Teacher ClassModel İşlemleri",
 *     description="Okul sınıf yönetimi"
 * )
 */
class ClassModelController extends Controller
{
    use ApiResponser;

    private function authorizeSchool(School $school)
    {
        $u = auth()->user();

        if ($u->hasRole('admin')) return true;

        if ($u->hasRole('manager')) {
            if (!$u->managerProfile || $u->managerProfile->school_id !== $school->id) {
                abort(403, 'Sadece kendi okulunuzda işlem yapabilirsiniz.');
            }
            return true;
        }

        if ($u->hasRole('teacher')) {
            if (!$u->teacherProfile || $u->teacherProfile->school_id !== $school->id) {
                abort(403, 'Sadece kendi okulunuzda işlem yapabilirsiniz.');
            }
            return true;
        }

        abort(403, 'Bu işlemi yapmak için yetkiniz yok.');
    }

    // ----------------------------------------------------------------------


    /**
     * @OA\Get(
     *     path="/api/schools/{school}/classes",
     *     summary="Okuldaki tüm sınıfları getirir",
     *     tags={"Manager & Teacher ClassModel İşlemleri"},
     *     security={{"bearerAuth":{}}},     
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         description="School ID",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(response=200, description="Sınıflar listelendi.")
     * )
     */
    public function index(School $school)
    {
        //TODO: yetki kontrolü eklenecek
        if (!auth()->user()->can('classmodel.view')) {
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }
        return $this->successResponse(
            ClassModel::where('school_id', $school->id)->get(),
            "Sınıflar başarıyla listelendi.",
            200
        );
    }

    /**
     * @OA\Post(
     *     path="/api/schools/{school}/classes",
     *     summary="Yeni sınıf oluşturur",
     *     tags={"Manager & Teacher ClassModel İşlemleri"},
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
     *         @OA\JsonContent(ref="#/components/schemas/StoreClassRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Sınıf başarıyla oluşturuldu."
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Yetki hatası"
     *     )
     * )
     */
    public function store(StoreClassRequest $request, School $school)
    {
        if (!auth()->user()->can('classmodel.create')) {
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }
        $this->authorizeSchoolAccess($school);
        // ✔ 1) Grade bu okula ait mi?
        $allowed = SchoolHasGrade::where('school_id', $school->id)
            ->where('grade_id', $request->grade_id)
            ->exists();

        if (!$allowed) {
            return $this->errorResponse(
                "Bu okul belirtilen grade seviyesine sahip değildir.",
                422
            );
        }
        $data = $request->validated();
        $data['school_id'] = $school->id;

        $class = ClassModel::create($data);

        return $this->successResponse($class, "Sınıf başarıyla oluşturuldu.", 201);
    }

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/classes/{classModel}",
     *     summary="Belirli bir sınıfı getirir",
     *     tags={"Manager & Teacher ClassModel İşlemleri"},
     *     security={{"bearerAuth":{}}},
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
     *         description="classModel ID",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(response=200, description="Sınıf detayları getirildi."),
     *     @OA\Response(response=403, description="Bu işlemi yapmak için yetkiniz yok."),
     * )
     */
    public function show(School $school, ClassModel $classModel)
    {
        $this->authorizeSchoolAccess($school);
        if (!auth()->user()->can('classmodel.view')) {
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }
        if ($classModel->school_id !== $school->id) {
            return $this->errorResponse("Bu sınıf bu okula ait değil.", 403);
        }

        return $this->successResponse($classModel, "Sınıf bilgileri getirildi.", 200);
    }

    /**
     * @OA\Put(
     *     path="/api/schools/{school}/classes/{classModel}",
     *     summary="Sınıfı günceller",
     *     tags={"Manager & Teacher ClassModel İşlemleri"},
     *     security={{"bearerAuth":{}}},
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
     *         description="classModel ID",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateClassRequest")),
     *     @OA\Response(response=200, description="Sınıf güncellendi.")
     * )
     */
    public function update(UpdateClassRequest $request, School $school, ClassModel $classModel)
    {
        $this->authorizeSchoolAccess($school);
        if (!auth()->user()->can('classmodel.update')) {
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }
        if ($classModel->school_id !== $school->id) {
            return $this->errorResponse("Bu sınıf bu okula ait değil.", 403);
        }
        // ✔ grade değişiyorsa kontrol et
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
        $classModel->update($request->validated());

        return $this->successResponse($classModel, "Sınıf başarıyla güncellendi.", 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/schools/{school}/classes/{classModel}",
     *     summary="Sınıfı siler",
     *     tags={"Manager & Teacher ClassModel İşlemleri"},
     *     security={{"bearerAuth":{}}},
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
     *         description="classModel ID",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(response=200, description="Sınıf silindi.")
     * )
     */
    public function destroy(School $school, ClassModel $classModel)
    {
        $this->authorizeSchoolAccess($school);
        if (!auth()->user()->can('classmodel.delete')) {
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }
        if ($classModel->school_id !== $school->id) {
            return $this->errorResponse("Bu sınıf bu okula ait değil.", 403);
        }

        $classModel->delete();

        return $this->successResponse(null, "Sınıf başarıyla silindi.", 200);
    }
    private function authorizeSchoolAccess(School $school)
    {
        $user = auth()->user();

        // Admin her okula erişebilir
        if ($user->hasRole('admin')) {
            return true;
        }

        // Manager kendi okulunda işlem yapabilir
        if ($user->hasRole('manager')) {
            if ($user->managerProfile && $user->managerProfile->school_id == $school->id) {
                return true;
            }
            abort(403, 'Bu işlem için yetkiniz yok. (Manager Okul Erişim Engeli)');
        }

        // Teacher kendi okulunda işlem yapabilir
        if ($user->hasRole('teacher')) {
            if ($user->teacherProfile && $user->teacherProfile->school_id == $school->id) {
                return true;
            }
            abort(403, 'Bu işlem için yetkiniz yok. (Teacher Okul Erişim Engeli)');
        }

        // Diğer roller için yasak
        abort(403, 'Bu işlem için yetkiniz yok.');
    }
}
