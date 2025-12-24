<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Birthday Checks",
 *     description="Öğrenci, veli ve öğretmen doğum günü kontrolleri"
 * )
 */
class BirthDayCheckController extends Controller
{
    use ApiResponser;
    /**
     * @OA\Get(
     *     path="/api/birthdays/parents",
     *     summary="Okuldaki velilerin doğum günlerini listeler",
     *     tags={"Birthday Checks"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Veli doğum günleri listelendi",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Veliler başarıyla listelendi."),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="name", type="string", example="Ahmet Yılmaz"),
     *                     @OA\Property(property="YAKINLIG", type="string", example="Anne"),
     *                     @OA\Property(property="veli adı", type="string", example="Ayşe Yılmaz"),
     *                     @OA\Property(property="birth_date", type="string", format="date", example="1980-05-12")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=403, description="Yetkisiz erişim")
     * )
     */
    public function getParentBirthDays(Request $request)
    {
        if (!auth()->user()->can('birthdays.view.detail')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }
        $user = auth()->user();
        $schoolId = $user->managerProfile->school_id;


        $parents = User::whereHas('schoolStudentProfile', function ($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        })
            ->with('schoolStudentProfile.parents')
            ->get()
            ->flatMap(function ($user) {
                return $user->schoolStudentProfile->parents->map(function ($parent) use ($user) {
                    return [
                        'name'       => $user->name,
                        'YAKINLIG'   => $parent->relationship,
                        'veli adı'   => $parent->name,
                        'birth_date' => $parent->birth_date,
                    ];
                });
            })
            ->values();


        return $this->successResponse($parents, 'Veliler başarıyla listelendi.', 200);

        $user = auth()->user();
        if ($user->hasRole('teacher')) {
            $classroom = ClassModel::where('teacher_id', $user->teacherProfile->id)->get();

            $classroomTeachers = $user->teacherProfile->classroomTeachers;
            return $this->errorResponse('Sadece veliler bu işlemi yapabilir.', 403);
        }






        $date = $request->input('date');

        if (!$date) {
            return response()->json(['error' => 'Tarih parametresi eksik.'], 400);
        }

        $today = now()->format('m-d');
        $inputDate = \Carbon\Carbon::parse($date)->format('m-d');

        $isBirthday = $today === $inputDate;

        return response()->json(['is_birthday' => $isBirthday]);
    }
    /**
     * @OA\Get(
     *     path="/api/birthdays/students",
     *     summary="Öğrencilerin doğum günlerini kontrol eder",
     *     tags={"Birthday Checks"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         required=true,
     *         description="Kontrol edilecek tarih",
     *         @OA\Schema(type="string", format="date", example="2025-05-12")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Doğum günü kontrolü",
     *         @OA\JsonContent(
     *             @OA\Property(property="is_birthday", type="boolean", example=true)
     *         )
     *     ),
     *
     *     @OA\Response(response=403, description="Yetkisiz işlem"),
     *     @OA\Response(response=400, description="Tarih parametresi eksik")
     * )
     */
    public function getStudentBirthDays(Request $request)
    {
        if (!auth()->user()->can('studentbirthdays.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }
        $date = $request->input('date');

        if (!$date) {
            return response()->json(['error' => 'Tarih parametresi eksik.'], 400);
        }

        $inputDate = \Carbon\Carbon::parse($date)->format('m-d');

        $isBirthday = $today === $inputDate;

        return response()->json(['is_birthday' => $isBirthday]);
    }



    /**
     * @OA\Get(
     *     path="/api/birthdays/teachers",
     *     summary="Öğretmenlerin doğum günlerini listeler",
     *     tags={"Birthday Checks"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Öğretmenler listelendi",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Öğretmenler başarıyla listelendi."),
     *             @OA\Property(property="data", type="object",
     *                 example={"Ali Öğretmen":"1985-04-12","Ayşe Öğretmen":"1990-09-21"}
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Öğretmen bulunamadı")
     * )
     */
    public function getTeacherBirthDays(Request $request)
    {
        if (!auth()->user()->can('teacherbirthdays.view.detail')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }
        //$teachers = User::role('teacher')->with('teacherProfile')->get()->value(['teacherProfile.birth_date,name']);
        $teachers = User::role('teacher')->with('teacherProfile')
            ->get()->pluck('teacherProfile.birth_date', 'name');

        if (!$teachers) {
            return $this->errorResponse('Hiç öğretmen bulunamadı.', 404);
        }


        $mytime = Carbon::now();
        return $this->successResponse($teachers, 'Öğretmenler başarıyla listelendi.', 200);
    }
}
