<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassModel;
use Illuminate\Support\Facades\DB;
use App\Models\User;

use App\Http\Requests\Class\StoreClassRequest;
use App\Http\Requests\Class\UpdateClassRequest;

/**
 * @OA\Tag(
 *     name="ClassModels",
 *     description="Class management endpoints for schools"
 * )
 */
class ClassModelController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/schools/{school_id}/classmodels",
     *     summary="Belirli bir okula ait tüm sınıf modellerini getirir",
     *     tags={"ClassModels"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="school_id",
     *         in="path",
     *         required=true,
     *         description="Okul ID'si",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı sorgu",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/ClassModel"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bu okula ait sınıf bulunamadı"
     *     )
     * )
     */
    public function getBySchool($school_id)
    {
        $this->authorize('viewAny', ClassModel::class);

        $classes = ClassModel::where('school_id', $school_id)->get();

        if ($classes->isEmpty()) {
            return response()->json(['message' => 'Bu okula ait sınıf bulunamadı.'], 404);
        }

        return response()->json($classes);
    }

    /**
     * @OA\Get(
     *     path="/api/classmodels/{id}",
     *     summary="ID’ye göre bir sınıf modelini getirir",
     *     tags={"ClassModels"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ClassModel ID'si",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="ClassModel bulundu",
     *         @OA\JsonContent(ref="#/components/schemas/ClassModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="ClassModel bulunamadı"
     *     )
     * )
     */
    public function getClassModelById($id)
    {
        $classModel = ClassModel::find($id);

        if (!$classModel) {
            return response()->json(['message' => 'ClassModel bulunamadı.'], 404);
        }
        // Policy kontrolü
        $this->authorize('view', $classModel);
        return response()->json($classModel);
    }
    /**
     * @OA\Post(
     *     path="/api/schools/{school}/class",
     *     summary="Create a new class",
     *     tags={"ClassModels"},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreClassRequest")
     *     ),
     *     @OA\Response(response=201, description="Class created successfully"),
     *     @OA\Response(response=400, description="No teachers found or invalid teacher"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function store(StoreClassRequest $request, $school)
    {
        $this->authorize('create', ClassModel::class);

        $user = $request->user();

        if (!in_array($user->role, ['admin', 'manager'])) {
            return response()->json(['message' => 'You are not authorized to create a class.'], 403);
        }

        $teacherExists = User::where('role', 'teacher')
            ->whereHas('teacherProfile', fn($q) => $q->where('school_id', $school))
            ->exists();

        if (!$teacherExists) {
            return response()->json([
                'message' => 'No teachers found in this school. Please create a teacher before creating a class.'
            ], 400);
        }

        $data = $request->validated();

        $teacher = User::where('id', $data['teacher_id'])
            ->where('role', 'teacher')
            ->whereHas('teacherProfile', fn($q) => $q->where('school_id', $school))
            ->first();

        if (!$teacher) {
            return response()->json([
                'message' => 'Selected teacher does not belong to this school.'
            ], 400);
        }

        $class = ClassModel::create([
            'name' => $data['name'],
            'school_id' => $school,
            'teacher_id' => $data['teacher_id'],
        ]);

        return response()->json([
            'message' => 'Class created successfully.',
            'class' => $class
        ], 201);
    }
    /**
     * @OA\Put(
     *     path="/api/schools/{school}/class/{classModel}",
     *     summary="Update an existing class",
     *     tags={"ClassModels"},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="classModel", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateClassRequest")
     *     ),
     *     @OA\Response(response=200, description="Class updated successfully"),
     *     @OA\Response(response=400, description="Invalid class or teacher"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function update(UpdateClassRequest $request, $school, ClassModel $classModel)
    {
        $this->authorize('update', $classModel);

        $user = $request->user();

        if (!in_array($user->role, ['admin', 'manager'])) {
            return response()->json(['message' => 'You are not authorized to update this class.'], 403);
        }

        if ($classModel->school_id != $school) {
            return response()->json(['message' => 'This class does not belong to the given school.'], 400);
        }

        $data = $request->validated();

        if (isset($data['teacher_id'])) {
            $teacher = User::where('id', $data['teacher_id'])
                ->where('role', 'teacher')
                ->whereHas('teacherProfile', fn($q) => $q->where('school_id', $school))
                ->first();

            if (!$teacher) {
                return response()->json([
                    'message' => 'The selected teacher does not belong to this school.'
                ], 400);
            }
        }

        $classModel->update($data);

        return response()->json([
            'message' => 'Class updated successfully.',
            'class' => $classModel
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/schools/{school}/class/{classModel}",
     *     summary="Delete a class",
     *     tags={"ClassModels"},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="classModel", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Class deleted successfully"),
     *     @OA\Response(response=400, description="Invalid school or class"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function destroy(Request $request, $school, ClassModel $classModel)
    {
        $this->authorize('delete', $classModel);

        $user = $request->user();

        if (!in_array($user->role, ['admin', 'manager'])) {
            return response()->json(['message' => 'You are not authorized to delete this class.'], 403);
        }

        if ($classModel->school_id != $school) {
            return response()->json(['message' => 'This class does not belong to this school.'], 400);
        }

        $classModel->delete();

        return response()->json(['message' => 'Class deleted successfully.']);
    }
}
