<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Http\Requests\Profile\SchoolStudentUpdateProfileRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\School;
use App\Models\ClassModel;
use App\Models\SchoolStudentProfile;
use App\Models\User;
use App\Http\Requests\Student\Crud\CreateStudentRequest;
use App\Http\Requests\Student\Crud\CreateStudentProfileRequest;
use App\Http\Requests\Student\Crud\UpdateStudentRequest;
use App\Http\Requests\Student\Crud\UpdateStudentProfileRequest;

class SchoolStudentController extends Controller
{
    //TODO :ADDİTİONAL CLASSROOM İÇİN AYNI ŞEY YAPILACAK.
    use ApiResponser;
    public function update(SchoolStudentUpdateProfileRequest $request)
    {
        $data = [];
        $user = $request->user();
        $updatedFields = [];

        // Sadece name doluysa onu güncelle
        if ($request->filled('name')) {
            $data['name'] = $request->name;
            $updatedFields[] = 'İsim';
        }

        // Sadece password doluysa hashleyip güncelle
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
            $updatedFields[] = 'şifre';
        }

        $user->update($data);
        if (empty($updatedFields)) {
            $message = 'Herhangi bir değişiklik yapılmadı.';
        } elseif (count($updatedFields) === 1) {
            $message = ucfirst($updatedFields[0]) . ' başarıyla güncellendi.';
        } else {
            $last = array_pop($updatedFields);
            $message = ucfirst(implode(', ', $updatedFields)) . ' ve ' . $last . ' başarıyla güncellendi.';
        }

        return $this->successResponse($user->fresh(), $message);
    }
    public function index(Request $request, School $school)
    {
        $school = $request->user()->school;
        $students = $school->students()
            ->with('user')
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->user?->name,
                    'email' => $student->user?->email,
                    'is_active' => $student->is_active,
                ];
            });

        return $this->successResponse($students, 'Öğrenci listesi başarıyla getirildi.');
    }
    /**
     * Sınıf ID’ye göre öğrencileri getir
     */
    public function getstudentsbyclassid(Request $request, School $school, ClassModel $classModel)
    {

        $this->authorize('student.view');

        $students = SchoolStudentProfile::where('schoolId', $school->id)
            ->where('active_class_id', $classModel->id)
            ->with('user')
            ->get();


        return $this->successResponse($students);
    }
    /**
     * Öğrenci ID’ye göre getir
     */
    public function show(Request $request, $id)
    {
        $this->authorize('student.view');

        $student = SchoolStudentProfile::with('user')->find($id);
        if (!$student) {
            return $this->errorResponse('Öğrenci bulunamadı.', 404);
        }

        return $this->successResponse($student);
    }
    /**
     * Yeni öğrenci oluştur (User tablosu)
     */
    public function store(CreateStudentRequest $request)
    {
        $this->authorize('student.create');

        $user = User::create([
            'name' => $request->name,
            'userName' => $request->userName,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'schoolstudent',
            'is_active' => true,
        ]);

        return $this->successResponse($user, 'Öğrenci başarıyla oluşturuldu.', 201);
    }

    /**
     * Öğrenci profilini oluştur (SchoolStudentProfile)
     */
    public function createstudentprofile(CreateStudentProfileRequest $request)
    {
        $this->authorize('student.create');

        $profile = SchoolStudentProfile::create([
            'user_id' => $request->user_id,
            'schoolId' => $request->schoolId,
            'phone' => $request->phone,
            'address' => $request->address,
            'birth_date' => $request->birth_date,
            'student_number' => $request->student_number,
            'tc_no' => $request->tc_no,
            'gender' => $request->gender,
            'description' => $request->description,
            'registered_at' => now(),
            'status' => 'active',
        ]);

        return $this->successResponse($profile, 'Öğrenci profili başarıyla oluşturuldu.', 201);
    }

    /**
     * Öğrenci güncelle (User)
     */
    public function updateById(UpdateStudentRequest $request, $id)
    {
        $this->authorize('student.update');

        $user = User::find($id);
        if (!$user) {
            return $this->errorResponse('Öğrenci bulunamadı.', 404);
        }

        $user->update([
            'name' => $request->name ?? $user->name,
            'email' => $request->email ?? $user->email,
            'is_active' => $request->is_active ?? $user->is_active,
        ]);

        return $this->successResponse($user, 'Öğrenci bilgileri güncellendi.', 200);
    }

    /**
     * Öğrenci profilini güncelle (SchoolStudentProfile)
     */
    public function updateProfileSettingsById(UpdateStudentProfileRequest $request, $id)
    {
        $this->authorize('student.update');

        $profile = SchoolStudentProfile::find($id);
        if (!$profile) {
            return $this->errorResponse('Profil bulunamadı.', 404);
        }

        $profile->update($request->validated());

        return $this->successResponse($profile, 'Öğrenci profili güncellendi.', 200);
    }

    /**
     * Öğrenci sil (User + ilişkili profile)
     */
    public function destroy(Request $request, $id)
    {
        $this->authorize('student.delete');

        $user = User::find($id);
        if (!$user) {
            return $this->errorResponse('Öğrenci bulunamadı.', 404);
        }

        $user->delete();

        return $this->successResponse([], 'Öğrenci ve ilişkili bilgiler silindi.', 200);
    }
}
