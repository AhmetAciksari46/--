<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ClassModel;

class SchoolStudentPolicy
{
    /**
     * Admin zaten bypass edilir (AuthServiceProvider::Gate::before)
     * Sadece manager veya ilgili sınıfın öğretmeni yeni öğrenci oluşturabilir.
     */
    public function create(User $user, ClassModel $classModel): bool
    {
        // Manager ise kendi okuluna ait sınıfta oluşturabilir
        if ($user->role === 'manager') {
            return optional($user->teacherProfile)->school_id === $classModel->school_id;
        }

        // Öğretmen ise kendi sınıfında öğrenci oluşturabilir
        if ($user->role === 'teacher') {
            return $user->id === $classModel->teacher_id;
        }

        return false;
    }

    /**
     * Manager veya sınıf öğretmeni kendi öğrencisini görebilir.
     */
    public function view(User $user, User $student): bool
    {
        if ($user->role === 'manager') {
            return optional($user->teacherProfile)->school_id === optional($student->schoolStudentProfile)->schoolId;
        }

        if ($user->role === 'teacher') {
            return $user->id === optional($student->schoolStudentProfile)->active_class_model_id;
        }

        return false;
    }
    /**
     * Öğrenci güncelleme — manager veya o sınıfın öğretmeni
     */
    public function update(User $user, User $student): bool
    {
        if ($user->role === 'manager') {
            return optional($user->teacherProfile)->school_id === optional($student->schoolStudentProfile)->schoolId;
        }

        if ($user->role === 'teacher') {
            $studentClassId = optional($student->schoolStudentProfile)->active_class_model_id;
            return $studentClassId && $user->id === optional($student->schoolStudentProfile->classModel)->teacher_id;
        }

        return false;
    }

    /**
     * Öğrenciyi silebilenler: manager veya öğrencinin öğretmeni.
     */
    public function delete(User $user, User $student): bool
    {
        if ($user->role === 'manager') {
            return optional($user->teacherProfile)->school_id === optional($student->schoolStudentProfile)->schoolId;
        }

        if ($user->role === 'teacher') {
            $studentClassId = optional($student->schoolStudentProfile)->active_class_model_id;
            return $studentClassId && $user->id === optional($student->schoolStudentProfile->classModel)->teacher_id;
        }

        return false;
    }
}
