<?php

namespace App\Http\Controllers;

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
     *     summary="Tüm global ders kayıtlarını listeler",
     *     tags={"Admin - Subject Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı işlem",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Subject"))
     *     ),
     *     @OA\Response(response=403, description="Yetkisiz erişim")
     * )
     */
    public function index()
    {
        $this->authorize('viewAny', Subject::class);

        $subjects = Subject::orderBy('id')->get();

        return $this->successResponse($subjects, 'Ders kayıtları başarıyla listelendi.');
    }

    /**
     * @OA\Get(
     *     path="/api/admin/subjects/{id}",
     *     summary="Belirli bir global dersin detayını getirir",
     *     tags={"Admin - Subject Management"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Ders ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı işlem",
     *         @OA\JsonContent(ref="#/components/schemas/Subject")
     *     ),
     *     @OA\Response(response=404, description="Kayıt bulunamadı"),
     *     @OA\Response(response=403, description="Yetkisiz erişim")
     * )
     */
    public function show(Subject $subject)
    {
        $this->authorize('view', $subject);

        return $this->successResponse($subject, 'Ders bilgisi başarıyla getirildi.');
    }

    /**
     * @OA\Post(
     *     path="/api/admin/subjects",
     *     summary="Yeni global ders oluşturur",
     *     tags={"Admin - Subject Management"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreSubjectRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Ders başarıyla oluşturuldu",
     *         @OA\JsonContent(ref="#/components/schemas/Subject")
     *     ),
     *     @OA\Response(response=422, description="Doğrulama hatası"),
     *     @OA\Response(response=403, description="Yetkisiz erişim")
     * )
     */
    public function store(StoreSubjectRequest $request)
    {
        $this->authorize('create', Subject::class);

        $subject = Subject::create($request->validated());

        return $this->successResponse($subject, 'Ders başarıyla oluşturuldu.', 201);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/subjects/{id}",
     *     summary="Mevcut bir dersi günceller",
     *     tags={"Admin - Subject Management"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Ders ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateSubjectRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ders başarıyla güncellendi",
     *         @OA\JsonContent(ref="#/components/schemas/Subject")
     *     ),
     *     @OA\Response(response=404, description="Kayıt bulunamadı"),
     *     @OA\Response(response=422, description="Doğrulama hatası")
     * )
     */
    public function update(UpdateSubjectRequest $request, Subject $subject, $id)
    {
        $this->authorize('update', $subject);

        $subject = Subject::find($id);

        if (!$subject) {
            return $this->errorResponse('Ders kaydı bulunamadı.', 404);
        }

        $this->authorize('update', $subject);

        $subject->update($request->validated());

        return $this->successResponse($subject->refresh(), 'Ders başarıyla güncellendi.');
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/subjects/{id}",
     *     summary="Belirli bir dersi siler",
     *     tags={"Admin - Subject Management"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Ders ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=204, description="Ders başarıyla silindi"),
     *     @OA\Response(response=403, description="Yetkisiz erişim"),
     *     @OA\Response(response=404, description="Kayıt bulunamadı")
     * )
     */
    public function destroy(Subject $subject)
    {
        $this->authorize('delete', $subject);

        $subject->delete();

        return $this->successResponse(null, 'Ders başarıyla silindi.', 204);
    }
}
