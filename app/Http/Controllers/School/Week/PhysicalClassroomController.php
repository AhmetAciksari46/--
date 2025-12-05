<?php

namespace App\Http\Controllers\School\Week;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Models\School;
use App\Models\PhysicalClassroom;
use App\Http\Requests\Classroom\PhysicalClassroomUpdateRequest;
use App\Http\Requests\Classroom\PhysicalClassroomStoreRequest;

/**
 * @OA\Tag(
 *     name="Manager & Teacher Physical Classrooms",
 *     description="Okul içi fiziksel derslik CRUD işlemleri"
 * )
 */
class PhysicalClassroomController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/classrooms",
     *     summary="Okuldaki tüm fiziksel derslikleri listeler",
     *     tags={"Manager & Teacher Physical Classrooms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List of classrooms")
     * )
     */
    public function index(School $school)
    {
        if (!auth()->user()->can('classroom.view',)) {
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }

        $classrooms = PhysicalClassroom::where('school_id', $school->id)->get();
        return $this->successResponse($classrooms, 'Sınıflar getirildi');
    }

    /**
     * @OA\Post(
     *     path="/api/schools/{school}/classrooms",
     *     summary="Yeni fiziksel derslik oluşturur",
     *     tags={"Manager & Teacher Physical Classrooms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/PhysicalClassroomStoreRequest")),
     *     @OA\Response(response=201, description="Classroom created")
     * )
     */
    public function store(PhysicalClassroomStoreRequest $request, School $school)
    {
        if (!auth()->user()->can('classroom.create',)) {
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }

        $data = $request->validated();
        $data['school_id'] = $school->id;

        $classroom = PhysicalClassroom::create($data);

        return $this->successResponse($classroom, 'Sınıf oluşturuldu', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/classrooms/{classroom}",
     *     summary="Belirli fiziksel derslik bilgisi",
     *     tags={"Manager & Teacher Physical Classrooms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="school", in="path", required=true),
     *     @OA\Parameter(name="classroom", in="path", required=true),
     *     @OA\Response(response=200, description="Classroom details")
     * )
     */
    public function show(School $school, PhysicalClassroom $classroom)
    {
        if (!auth()->user()->can('classroom.view',)) {
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }
        if ($classroom->school_id !== $school->id) {
            return $this->errorResponse('Bu sınıf bu okula ait değil', 403);
        }
        return $this->successResponse($classroom, 'Sınıf bilgisi getirildi');
    }

    /**
     * @OA\Put(
     *     path="/api/schools/{school}/classrooms/{classroom}",
     *     summary="Fiziksel derslik günceller",
     *     tags={"Manager & Teacher Physical Classrooms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/PhysicalClassroomUpdateRequest")),
     *     @OA\Response(response=200, description="Classroom updated")
     * )
     */
    public function update(PhysicalClassroomUpdateRequest $request, School $school, PhysicalClassroom $classroom)
    {
        if (!auth()->user()->can('classroom.update',)) {
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }
        if ($classroom->school_id !== $school->id) {
            return $this->errorResponse('Bu sınıf bu okula ait değil', 403);
        }

        $classroom->update($request->validated());
        return $this->successResponse($classroom, 'Sınıf güncellendi');
    }

    /**
     * @OA\Delete(
     *     path="/api/schools/{school}/classrooms/{classroom}",
     *     summary="Fiziksel derslik siler",
     *     tags={"Manager & Teacher Physical Classrooms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Classroom deleted")
     * )
     */
    public function destroy(School $school, PhysicalClassroom $classroom)
    {
        if (!auth()->user()->can('classroom.delete',)) {
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }
        if ($classroom->school_id !== $school->id) {
            return $this->errorResponse('Bu sınıf bu okula ait değil', 403);
        }

        $classroom->delete();
        return $this->successResponse(null, 'Sınıf silindi');
    }
}
