<?php
//TODO: CONTROLLER İSMİ YANLIŞ OLMUŞ. DOĞRUSU SchoolSessionController OLMALI
namespace App\Http\Controllers\School\Week;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\SchoolSession;
use App\Http\Requests\School\StoreSchoolSessionRequest;
use App\Http\Requests\School\UpdateSchoolSessionRequest;
use App\Traits\ApiResponser;

/**
 * @OA\Tag(
 * name="School Session Management",
 * description="Ders Oturumu (SchoolSession) Yönetimi (Manager/Teacher)"
 * )
 */
class SchoolSectionController extends Controller
{
    use ApiResponser;




    /**
     * @OA\Get(
     * path="/api/schools/{school}/manager/sessions",
     * operationId="listSchoolSessions",
     * tags={"School Session Management"},
     * summary="Okuldaki tüm ders oturumlarını listeler (Teacher ise kendi oturumları)",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\Response(response=200, description="Başarılı", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/SchoolSession"))),
     * @OA\Response(response=403, description="Yetkisiz Erişim")
     * )
     */
    public function index(School $school)
    {
        $this->authorize('viewAny', SchoolSession::class);

        $query = $school->sessions()->with(['classModel', 'teacher', 'subject']);

        // Öğretmen ise sadece kendi oturumlarını görsün
        if (auth()->user()->isTeacher()) {
            $query->where('teacher_id', auth()->id());
        }

        $sessions = $query->get();
        return $this->successResponse($sessions);
    }

    /**
     * @OA\Get(
     * path="/api/schools/{school}/manager/sessions/{session}",
     * operationId="showSchoolSession",
     * tags={"School Session Management"},
     * summary="Belirli bir ders oturumunun detaylarını getirir",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\Parameter(name="session", in="path", required=true, @OA\Schema(type="integer"), description="Ders Oturumu ID"),
     * @OA\Response(response=200, description="Başarılı", @OA\JsonContent(ref="#/components/schemas/SchoolSession")),
     * @OA\Response(response=404, description="Oturum bulunamadı veya okula ait değil")
     * )
     */
    public function show(School $school, SchoolSession $session)
    {
        $this->authorize('view', $session);

        if ($session->school_id !== $school->id) {
            return response()->json(['message' => 'Oturum kaydı belirtilen okula ait değil.'], 404);
        }
        return $this->successResponse($session->load(['classModel', 'teacher', 'subject']));
    }





    /**
     * @OA\Post(
     * path="/api/schools/{school}/manager/sessions",
     * operationId="storeSchoolSession",
     * tags={"School Session Management"},
     * summary="Yeni bir ders oturumu kaydı oluşturur",
     * security={{"sanctum": {}}},
     * @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StoreSchoolSessionRequest")),
     * @OA\Response(response=201, description="Başarılı", @OA\JsonContent(ref="#/components/schemas/SchoolSession")),
     * @OA\Response(response=403, description="Yetkisiz Erişim")
     * )
     */


    public function store(StoreSchoolSessionRequest $request, School $school)
    {
        $this->authorize('create', SchoolSession::class);

        $session = $school->sessions()->create($request->validated());
        return $this->successResponse($session, 201);
    }

    /**
     * @OA\Put(
     * path="/api/schools/{school}/manager/sessions/{session}",
     * operationId="updateSchoolSession",
     * tags={"School Session Management"},
     * summary="Belirli bir ders oturumunu günceller",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\Parameter(name="session", in="path", required=true, @OA\Schema(type="integer"), description="Ders Oturumu ID"),
     * @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateSchoolSessionRequest")),
     * @OA\Response(response=200, description="Başarılı", @OA\JsonContent(ref="#/components/schemas/SchoolSession")),
     * @OA\Response(response=404, description="Oturum bulunamadı veya okula ait değil")
     * )
     */
    public function update(UpdateSchoolSessionRequest $request, School $school, SchoolSession $session)
    {
        $this->authorize('update', $session);

        if ($session->school_id !== $school->id) {
            return response()->json(['message' => 'Oturum kaydı belirtilen okula ait değil.'], 404);
        }

        $session->update($request->validated());

        return $this->successResponse($session);
    }

    /**
     * @OA\Delete(
     * path="/api/schools/{school}/manager/sessions/{session}",
     * operationId="deleteSchoolSession",
     * tags={"School Session Management"},
     * summary="Belirli bir ders oturumunu siler",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\Parameter(name="session", in="path", required=true, @OA\Schema(type="integer"), description="Ders Oturumu ID"),
     * @OA\Response(response=204, description="Başarılı (İçerik Yok)"),
     * @OA\Response(response=404, description="Oturum bulunamadı veya okula ait değil")
     * )
     */
    public function destroy(School $school, SchoolSession $session)
    {
        $this->authorize('delete', $session);

        if ($session->school_id !== $school->id) {
            return response()->json(['message' => 'Oturum kaydı belirtilen okula ait değil.'], 404);
        }

        $session->delete();
        return $this->successResponse(null, 204);
    }
}
