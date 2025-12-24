<?php

namespace App\Http\Controllers\School\General;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponser;
use App\Models\School;
use App\Models\StudentPreRegistration;
use App\Http\Requests\StudentPreRegistration\{
    StorePreRegistrationRequest,
    ApprovePreRegistrationRequest
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\Student\PreRegistrationCommitService;

/**
 * @OA\Tag(
 *     name="Öğrenci Ön Kayıt",
 *     description="Öğrenci ön kayıt işlemleri"
 * )
 */
class StudentPreRegistrationController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/pre-registrations",
     *     summary="Ön kayıtları listeler",
     *     tags={"Öğrenci Ön Kayıt"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="school", in="path", required=true),
     *     @OA\Response(response=200, description="Liste getirildi")
     * )
     */
    public function index(School $school)
    {
        if (!auth()->user()->can('student.pre_register.view')) {
            return $this->errorResponse('Ön kayıtları görüntüleme yetkiniz yok.', 403);
        }

        $items = StudentPreRegistration::where('school_id', $school->id)->latest()->get();

        return $this->successResponse($items, 'Ön kayıtlar listelendi.');
    }

    /**
     * @OA\Post(
     *     path="/api/schools/{school}/pre-registrations",
     *     summary="Yeni öğrenci ön kayıt oluşturur",
     *     tags={"Öğrenci Ön Kayıt"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(ref="#/components/schemas/StorePreRegistrationRequest"),
     *     @OA\Response(response=201, description="Ön kayıt oluşturuldu")
     * )
     */
    public function store(StorePreRegistrationRequest $request, School $school)
    {
        if (!auth()->user()->can('student.pre_register.create')) {
            return $this->errorResponse('Ön kayıt oluşturma yetkiniz yok.', 403);
        }

        $data = $request->validated();
        $data['school_id']  = $school->id;
        $data['created_by'] = auth()->id();
        $data['status']     = 'draft';

        $item = StudentPreRegistration::create($data);

        return $this->successResponse($item, 'Ön kayıt başarıyla oluşturuldu.', 201);
    }

    /**
     * @OA\Post(
     *     path="/api/pre-registrations/{preRegistration}/submit",
     *     summary="Ön kaydı incelemeye gönderir",
     *     tags={"Öğrenci Ön Kayıt"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Gönderildi")
     * )
     */
    public function submit(StudentPreRegistration $preRegistration)
    {
        if (!auth()->user()->can('student.pre_register.submit')) {
            return $this->errorResponse('Ön kaydı gönderme yetkiniz yok.', 403);
        }

        if ($preRegistration->status !== 'draft') {
            return $this->errorResponse('Sadece taslak durumundaki kayıtlar gönderilebilir.', 422);
        }

        $preRegistration->update([
            'status'       => 'submitted',
            'submitted_at' => now()
        ]);

        return $this->successResponse(
            $preRegistration,
            'Ön kayıt incelemeye gönderildi.'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/pre-registrations/{preRegistration}/approve",
     *     summary="Ön kaydı onaylayarak öğrenciye dönüştürür",
     *     tags={"Öğrenci Ön Kayıt"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(ref="#/components/schemas/ApprovePreRegistrationRequest"),
     *     @OA\Response(response=200, description="Öğrenci oluşturuldu")
     * )
     */
    public function approve(
        ApprovePreRegistrationRequest $request,
        StudentPreRegistration $preRegistration,
        PreRegistrationCommitService $commitService
    ) {
        if (!auth()->user()->can('student.pre_register.approve')) {
            return $this->errorResponse('Ön kayıt onaylama yetkiniz yok.', 403);
        }

        if ($preRegistration->status !== 'submitted') {
            return $this->errorResponse('Sadece gönderilmiş kayıtlar onaylanabilir.', 422);
        }

        $commitService->commit($preRegistration, $request->validated());

        return $this->successResponse(
            null,
            'Ön kayıt onaylandı ve öğrenci oluşturuldu.'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/pre-registrations/{preRegistration}/cancel",
     *     summary="Ön kaydı iptal eder",
     *     tags={"Öğrenci Ön Kayıt"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="İptal edildi")
     * )
     */
    public function cancel(StudentPreRegistration $preRegistration)
    {
        if (!auth()->user()->can('student.pre_register.cancel')) {
            return $this->errorResponse('Ön kayıt iptal etme yetkiniz yok.', 403);
        }

        if (!in_array($preRegistration->status, ['draft', 'submitted'])) {
            return $this->errorResponse('Bu kayıt iptal edilemez.', 422);
        }

        $preRegistration->update([
            'status'        => 'cancelled',
            'cancelled_at'  => now()
        ]);

        return $this->successResponse(
            $preRegistration,
            'Ön kayıt iptal edildi.'
        );
    }
}
