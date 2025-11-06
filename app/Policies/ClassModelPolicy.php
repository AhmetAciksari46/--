<?php

namespace App\Policies;

use App\Models\ClassModel;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClassModelPolicy
{
    /**
     * Admin dışındaki kullanıcıların yetkilerini sınırlıyoruz.
     * Admin Gate::before() ile zaten bypass edilir.
     */



    /**
     * Kullanıcı kendi okulundaki tüm sınıfları görebilir mi?
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['teacher', 'manager']);
    }

    /**
     * Kullanıcı belirli bir sınıfı görebilir mi?
     */
    public function view(User $user, ClassModel $classModel): bool
    {
        return in_array($user->role, ['teacher', 'manager'])
            && $user->school_id === $classModel->school_id;
    }



    /**
     * Manager sadece kendi okuluna bağlı sınıfları oluşturabilir.
     */
    public function create(User $user): bool
    {
        return $user->role === 'manager' && !empty(optional($user->teacherProfile)->school_id);
    }

    /**
     * Manager yalnızca kendi okulundaki sınıfları güncelleyebilir.
     */
    public function update(User $user, ClassModel $classModel): bool
    {
        if ($user->role !== 'manager') {
            return false;
        }

        return optional($user->teacherProfile)->school_id === $classModel->school_id;
    }

    /**
     * Manager yalnızca kendi okulundaki sınıfları silebilir.
     */
    public function delete(User $user, ClassModel $classModel): bool
    {
        if ($user->role !== 'manager') {
            return false;
        }

        return optional($user->teacherProfile)->school_id === $classModel->school_id;
    }
}
