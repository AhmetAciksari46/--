<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\Subject;
use App\Http\Requests\School\StoreSubjectRequest;
use App\Http\Requests\School\UpdateSubjectRequest;
use App\Traits\ApiResponser;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // ⬅️ EKLE

/**
 * @OA\Tag(
 *     name="Admin - Subject Management",
 *     description="Global Ders (Subject) Yönetimi. Bu uç noktalar yalnızca admin kullanıcılar içindir."
 * )
 */
class SubjectController extends Controller
{
    use ApiResponser, AuthorizesRequests;
    /**
     * @OA\Get(
     *     path="/api/admin/subjects",
     *     tags={"Subjects"},
     *     summary="Tüm dersleri listele",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Liste döndürüldü")
     * )
     */
    public function index()
    {
        $this->authorize('viewAny', Subject::class);
        if (!auth()->user()->can('subject.view.list')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        return $this->successResponse(Subject::with(['branch', 'grade'])->get(), 'Dersler başarıyla getirildi.', 200);
    }

    /**
     * @OA\Get(
     *   path="/api/manager/getactivesubjects",
     *   tags={"Manager Genel İşlemleri"},
     *   summary="Aktif olan branş listesini getir",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(
     *      response=200,
     *      description="Aktif branş listesi",
     *      @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="status", type="boolean", example=true),
     *          @OA\Property(property="message", type="string", example="İşlem başarılı."),
     *      )
     *   ),
     *   @OA\Response(
     *      response=403,
     *      description="Yetkiniz yok"
     *   )
     * )
     */
    public function activeSubjects()
    {
        if (!auth()->user()->can('subject.view.list')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        return $this->successResponse(Subject::with(['branch', 'grade'])->get(), 'Dersler başarıyla getirildi.', 200);
    }




    /**
     * @OA\Post(
     *     path="/api/admin/subjects",
     *     tags={"Subjects"},
     *     summary="Yeni ders oluştur",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","branch_id"},
     *             @OA\Property(property="name", type="string", example="English Reading"),
     *             @OA\Property(property="branch_id", type="integer", example=2),
     *             @OA\Property(property="grade_id", type="integer", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Oluşturuldu")
     * )
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('subject.create')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        $this->authorize('create', Subject::class);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:subjects,name',
            'branch_id' => 'required|exists:branches,id',
            'grade_id' => 'nullable|exists:grades,id',
        ]);

        $subject = Subject::create($validated);
        return $this->successResponse($subject, 'Ders başarıyla oluşturuldu.', 200);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/subjects/{id}",
     *     tags={"Subjects"},
     *     summary="Ders güncelle",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="branch_id", type="integer"),
     *             @OA\Property(property="grade_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Başarılı")
     * )
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('subject.update')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        $subject = Subject::findOrFail($id);
        $this->authorize('update', $subject);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:subjects,name,' . $id,
            'branch_id' => 'sometimes|exists:branches,id',
            'grade_id' => 'nullable|exists:grades,id',
        ]);

        $subject->update($validated);

        return $this->successResponse($subject, 'Ders başarıyla güncellendi.', 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/subjects/{id}",
     *     tags={"Subjects"},
     *     summary="Ders sil",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Silindi")
     * )
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('subject.delete')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        $subject = Subject::findOrFail($id);
        $this->authorize('delete', $subject);
        $subject->delete();

        return $this->successResponse(null, 'Silindi', 200);
    }
}
