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
 *     name="Doğum Günü",
 *     description="Öğrenci, veli ve öğretmen doğum günü kontrolleri"
 * )
 */
class BirthDayCheckController extends Controller
{
    use ApiResponser;



    /**
     * @OA\Get(
     *     path="/api/birthdays/students/countdowns",
     *     summary="Tüm öğrencilerin doğum gününe kalan gün sayısını döner",
     *     description="Bugünden itibaren her öğrencinin bir sonraki doğum gününe kaç gün kaldığını hesaplar. Örn: yarın doğum günü olan için 1, bugün olan için 0 döner. Eğer doğum günü bu yıl geçtiyse gelecek yılın doğum gününe göre hesaplanır.",
     *     tags={"Doğum Günü"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="max_days",
     *         in="query",
     *         required=false,
     *         description="Sadece belirtilen gün sayısı içinde doğum günü olan öğrencileri getirir. Örn: 7 gönderirsen önümüzdeki 7 gün içindeki doğum günlerini listeler.",
     *         @OA\Schema(type="integer", example=7)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Öğrencilerin doğum gününe kalan gün bilgileri",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Öğrencilerin doğum günü kalan günleri getirildi."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="student_id", type="integer", example=12),
     *                     @OA\Property(property="name", type="string", example="Ahmet Yılmaz"),
     *                     @OA\Property(property="birth_date", type="string", format="date", example="2010-12-26"),
     *                     @OA\Property(property="next_birthday", type="string", format="date", example="2025-12-26"),
     *                     @OA\Property(property="days_left", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Yetkisiz erişim",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Bu işlem için yetkiniz yok."),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Öğrenci bulunamadı",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Hiç öğrenci bulunamadı."),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     )
     * )
     */
    public function getStudentBirthdayCountdowns(Request $request)
    {
        if (!auth()->user()->can('studentbirthdays.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        $user = auth()->user();
        // ✅ Manager veya Teacher okul id belirleme
        $schoolId = null;

        if ($user->hasRole('manager')) {
            $schoolId = $user->managerProfile->school_id ?? null;
        } elseif ($user->hasRole('teacher')) {
            $schoolId = $user->teacherProfile->school_id ?? null;
        } else {
            return $this->errorResponse('Sadece manager veya teacher bu işlemi yapabilir.', 403);
        }

        if (!$schoolId) {
            return $this->errorResponse('Okul bilgisi bulunamadı.', 404);
        }

        // Opsiyonel filtre: sadece max_days içinde olanlar
        $maxDays = $request->query('max_days'); // örn 7

        $students = User::where('role', 'schoolstudent')
            ->whereHas('schoolStudentProfile', function ($q) use ($schoolId) {
                if ($schoolId) {
                    $q->where('school_id', $schoolId);
                }
            })
            ->with('schoolStudentProfile:id,user_id,birth_date')
            ->get();

        if ($students->isEmpty()) {
            return $this->errorResponse('Hiç öğrenci bulunamadı.', 404);
        }

        $today = Carbon::today();

        $data = $students->map(function ($student) use ($today, $maxDays) {

            $birthDate = $student->schoolStudentProfile->birth_date ?? null;

            if (!$birthDate) {
                return null;
            }

            $birth = Carbon::parse($birthDate);

            // Bu yılki doğum günü
            $nextBirthday = Carbon::create($today->year, $birth->month, $birth->day);

            // Eğer bu yıl geçtiyse → bir sonraki yıl
            if ($nextBirthday->lt($today)) {
                $nextBirthday->addYear();
            }

            $daysLeft = $today->diffInDays($nextBirthday);

            // max_days filtresi varsa uygula
            if ($maxDays !== null && $daysLeft > (int)$maxDays) {
                return null;
            }

            return [
                'student_id'     => $student->id,
                'name'           => $student->name,
                'birth_date'     => $birth->format('Y-m-d'),
                'next_birthday'  => $nextBirthday->format('Y-m-d'),
                'days_left'      => $daysLeft,
            ];
        })->filter()->values();

        return $this->successResponse($data, 'Öğrencilerin doğum günü kalan günleri getirildi.', 200);
    }



    /**
     * @OA\Get(
     *     path="/api/birthdays/teachers/countdowns",
     *     summary="Öğretmenlerin doğum gününe kalan gün sayısını döner",
     *     description="Bugünden itibaren öğretmenlerin bir sonraki doğum gününe kaç gün kaldığını hesaplar. Örn: yarın doğum günü olan için 1, bugün olan için 0 döner. Eğer doğum günü bu yıl geçtiyse gelecek yılın doğum gününe göre hesaplanır. Bu endpoint sadece manager veya teacher tarafından kullanılabilir.",
     *     tags={"Doğum Günü"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="max_days",
     *         in="query",
     *         required=false,
     *         description="Sadece belirtilen gün sayısı içinde doğum günü olan öğretmenleri getirir. Örn: 7 gönderirsen önümüzdeki 7 gün içindeki doğum günlerini listeler.",
     *         @OA\Schema(type="integer", example=7)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Öğretmenlerin doğum gününe kalan gün bilgileri",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Öğretmenlerin doğum günü kalan günleri getirildi."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="teacher_id", type="integer", example=10),
     *                     @OA\Property(property="name", type="string", example="Öğretmen Adı"),
     *                     @OA\Property(property="birth_date", type="string", format="date", example="1985-12-26"),
     *                     @OA\Property(property="next_birthday", type="string", format="date", example="2025-12-26"),
     *                     @OA\Property(property="days_left", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Yetkisiz erişim",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Bu işlem için yetkiniz yok."),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Öğretmen bulunamadı",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Hiç öğretmen bulunamadı."),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     )
     * )
     */
    public function getTeacherBirthdayCountdowns(Request $request)
    {
        // ✅ permission check (senin sisteminde hangisi uygunsa bunu kullan)
        if (!auth()->user()->can('teacherbirthdays.view.detail')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        $user = auth()->user();

        // ✅ Manager veya Teacher okul id belirleme
        $schoolId = null;

        if ($user->hasRole('manager')) {
            $schoolId = $user->managerProfile->school_id ?? null;
        } elseif ($user->hasRole('teacher')) {
            $schoolId = $user->teacherProfile->school_id ?? null;
        } else {
            return $this->errorResponse('Sadece manager veya teacher bu işlemi yapabilir.', 403);
        }

        if (!$schoolId) {
            return $this->errorResponse('Okul bilgisi bulunamadı.', 404);
        }

        // Opsiyonel filtre: sadece max_days içinde olanlar
        $maxDays = $request->query('max_days'); // örn 7

        // ✅ Aynı okula bağlı öğretmenleri çek
        $teachers = User::role('teacher')
            ->whereHas('teacherProfile', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->with('teacherProfile:id,user_id,school_id,birth_date')
            ->get();

        if ($teachers->isEmpty()) {
            return $this->errorResponse('Hiç öğretmen bulunamadı.', 404);
        }

        $today = Carbon::today();

        $data = $teachers->map(function ($teacher) use ($today, $maxDays) {

            $birthDate = $teacher->teacherProfile->birth_date ?? null;

            if (!$birthDate) {
                return null;
            }

            $birth = Carbon::parse($birthDate);

            // Bu yılki doğum günü
            $nextBirthday = Carbon::create($today->year, $birth->month, $birth->day);

            // Eğer bu yıl geçtiyse → bir sonraki yıl
            if ($nextBirthday->lt($today)) {
                $nextBirthday->addYear();
            }

            $daysLeft = $today->diffInDays($nextBirthday);

            // max_days filtresi varsa uygula
            if ($maxDays !== null && $daysLeft > (int)$maxDays) {
                return null;
            }

            return [
                'teacher_id'     => $teacher->id,
                'name'           => $teacher->name,
                'birth_date'     => $birth->format('Y-m-d'),
                'next_birthday'  => $nextBirthday->format('Y-m-d'),
                'days_left'      => $daysLeft,
            ];
        })->filter()->values();

        return $this->successResponse($data, 'Öğretmenlerin doğum günü kalan günleri getirildi.', 200);
    }


    /**
     * @OA\Get(
     *     path="/api/birthdays/parents/countdowns",
     *     summary="Öğrencilerin velilerinin doğum gününe kalan gün sayısını döner",
     *     description="Bugünden itibaren okul öğrencilerinin velilerinin (anne/baba vb.) bir sonraki doğum gününe kaç gün kaldığını hesaplar. Bir öğrencinin birden fazla velisi olabilir. Bu endpoint sadece manager veya teacher tarafından kullanılabilir.",
     *     tags={"Doğum Günü"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="max_days",
     *         in="query",
     *         required=false,
     *         description="Sadece belirtilen gün sayısı içinde doğum günü olan velileri getirir. Örn: 7 gönderirsen önümüzdeki 7 gün içindeki doğum günlerini listeler.",
     *         @OA\Schema(type="integer", example=7)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Velilerin doğum gününe kalan gün listesi",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Velilerin doğum günü kalan günleri getirildi."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="student_id", type="integer", example=45),
     *                     @OA\Property(property="student_name", type="string", example="Ahmet AçıkSarı"),
     *                     @OA\Property(property="parent_id", type="integer", example=12),
     *                     @OA\Property(property="relationship", type="string", example="Anne"),
     *                     @OA\Property(property="parent_name", type="string", example="Ayşe AçıkSarı"),
     *                     @OA\Property(property="birth_date", type="string", format="date", example="1985-12-27"),
     *                     @OA\Property(property="next_birthday", type="string", format="date", example="2025-12-27"),
     *                     @OA\Property(property="days_left", type="integer", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Yetkisiz erişim",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Bu işlem için yetkiniz yok."),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Kayıt bulunamadı",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Hiç veli bulunamadı."),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     )
     * )
     */
    public function getParentBirthdayCountdowns(Request $request)
    {
        if (!auth()->user()->can('parentbirthdays.view.detail')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        $user = auth()->user();

        // ✅ Manager veya Teacher okul id belirleme
        $schoolId = null;

        if ($user->hasRole('manager')) {
            $schoolId = $user->managerProfile->school_id ?? null;
        } elseif ($user->hasRole('teacher')) {
            $schoolId = $user->teacherProfile->school_id ?? null;
        } else {
            return $this->errorResponse('Sadece manager veya teacher bu işlemi yapabilir.', 403);
        }

        if (!$schoolId) {
            return $this->errorResponse('Okul bilgisi bulunamadı.', 404);
        }

        $maxDays = $request->query('max_days'); // örn 7
        $today = Carbon::today();

        // ✅ Okuldaki öğrenciler + parents
        $students = User::where('role', 'schoolstudent')
            ->whereHas('schoolStudentProfile', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->with('schoolStudentProfile.parents')
            ->get();

        if ($students->isEmpty()) {
            return $this->errorResponse('Hiç öğrenci bulunamadı.', 404);
        }

        // ✅ flatMap ile her parent ayrı satır
        $data = $students->flatMap(function ($student) use ($today, $maxDays) {

            $profile = $student->schoolStudentProfile;

            if (!$profile || $profile->parents->isEmpty()) {
                return [];
            }

            return $profile->parents->map(function ($parent) use ($student, $today, $maxDays) {

                if (!$parent->birth_date) {
                    return null;
                }

                $birth = Carbon::parse($parent->birth_date);

                // Bu yılki doğum günü
                $nextBirthday = Carbon::create($today->year, $birth->month, $birth->day);

                // Eğer bu yıl geçtiyse → bir sonraki yıl
                if ($nextBirthday->lt($today)) {
                    $nextBirthday->addYear();
                }

                $daysLeft = $today->diffInDays($nextBirthday);

                // max_days filtresi varsa uygula
                if ($maxDays !== null && $daysLeft > (int)$maxDays) {
                    return null;
                }

                return [
                    'student_id'     => $student->id,
                    'student_name'   => $student->name,
                    'parent_id'      => $parent->id,
                    'relationship'   => $parent->relationship, // Anne/Baba
                    'parent_name'    => $parent->name,
                    'birth_date'     => $birth->format('Y-m-d'),
                    'next_birthday'  => $nextBirthday->format('Y-m-d'),
                    'days_left'      => $daysLeft,
                ];
            });
        })
            ->filter()
            ->values();

        if ($data->isEmpty()) {
            return $this->errorResponse('Hiç veli bulunamadı.', 404);
        }

        return $this->successResponse($data, 'Velilerin doğum günü kalan günleri getirildi.', 200);
    }
}
