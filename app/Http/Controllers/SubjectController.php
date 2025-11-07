<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\Subject;
use App\Http\Requests\School\StoreSubjectRequest;
use App\Http\Requests\School\UpdateSubjectRequest;
use App\Traits\ApiResponser;

/**
 * @OA\Tag(
 * name="Subject Management",
 * description="Ders Kaynakları (Subject) Yönetimi (Admin/Manager)"
 * )
 */
class SubjectController extends Controller
{
    use ApiResponser;
    /**
     * @OA\Get(
     * path="/api/schools/{school}/admin/subjects",
     * operationId="listSubjects",
     * tags={"Subject Management"},
     * summary="Okula ait ders kayıtlarını listeler",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\Response(response=200, description="Başarılı", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Subject"))),
     * @OA\Response(response=403, description="Yetkisiz Erişim")
     * )
     */
    public function index(School $school)
    {
        $this->authorize('viewAny', Subject::class);

        $subjects = $school->subjects()->get();

        return $this->successResponse($subjects);
    }
    /**
     * @OA\Get(
     * path="/api/schools/{school}/admin/subjects/{subject}",
     * operationId="showSubject",
     * tags={"Subject Management"},
     * summary="Belirli bir dersin detayını getirir",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\Parameter(name="subject", in="path", required=true, @OA\Schema(type="integer"), description="Ders ID"),
     * @OA\Response(response=200, description="Başarılı", @OA\JsonContent(ref="#/components/schemas/Subject")),
     * @OA\Response(response=404, description="Ders bulunamadı veya okula ait değil")
     * )
     */
    public function show(School $school, Subject $subject)
    {
        $this->authorize('view', $subject);

        if ($subject->school_id !== $school->id) {
            return response()->json(['message' => 'Ders kaydı belirtilen okula ait değil.'], 404);
        }
        return $this->successResponse($subject);
    }


    /**
     * @OA\Post(
     * path="/api/schools/{school}/admin/subjects",
     * operationId="storeSubject",
     * tags={"Subject Management"},
     * summary="Yeni bir ders kaydı oluşturur (Okula atama yapılır)",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StoreSubjectRequest")),
     * @OA\Response(response=201, description="Başarılı", @OA\JsonContent(ref="#/components/schemas/Subject")),
     * @OA\Response(response=403, description="Yetkisiz Erişim")
     * )
     */
    public function store(StoreSubjectRequest $request, School $school)
    {
        $this->authorize('create', Subject::class);

        // Okula atama yaparak kaydı oluştur
        $subject = $school->subjects()->create($request->validated());

        return $this->successResponse($subject, 201);
    }

    /**
     * @OA\Put(
     * path="/api/schools/{school}/admin/subjects/{subject}",
     * operationId="updateSubject",
     * tags={"Subject Management"},
     * summary="Belirli bir ders kaydını günceller",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\Parameter(name="subject", in="path", required=true, @OA\Schema(type="integer"), description="Ders ID"),
     * @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateSubjectRequest")),
     * @OA\Response(response=200, description="Başarılı", @OA\JsonContent(ref="#/components/schemas/Subject")),
     * @OA\Response(response=404, description="Ders bulunamadı veya okula ait değil")
     * )
     */
    public function update(UpdateSubjectRequest $request, School $school, Subject $subject)
    {
        $this->authorize('update', $subject);

        if ($subject->school_id !== $school->id) {
            return response()->json(['message' => 'Ders kaydı belirtilen okula ait değil.'], 404);
        }

        $subject->update($request->validated());

        return $this->successResponse($subject);
    }

    /**
     * @OA\Delete(
     * path="/api/schools/{school}/admin/subjects/{subject}",
     * operationId="deleteSubject",
     * tags={"Subject Management"},
     * summary="Belirli bir ders kaydını siler",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\Parameter(name="subject", in="path", required=true, @OA\Schema(type="integer"), description="Ders ID"),
     * @OA\Response(response=204, description="Başarılı (İçerik Yok)"),
     * @OA\Response(response=404, description="Ders bulunamadı veya okula ait değil")
     * )
     */
    public function destroy(School $school, Subject $subject)
    {
        $this->authorize('delete', $subject);

        if ($subject->school_id !== $school->id) {
            return response()->json(['message' => 'Ders kaydı belirtilen okula ait değil.'], 404);
        }

        $subject->delete();
        return $this->successResponse(null, 204);
    }
}
