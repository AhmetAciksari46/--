<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }
    public function createTeacher(User $creator, array $attributes): bool|\Illuminate\Auth\Access\Response
{
    $targetRole     = $attributes['role_name'] ?? null;
    $targetSchoolId = $attributes['school_id'] ?? null;

    if (!$creator->hasAnyRole(['manager', 'teacher'])) {
        return Response::deny('Bu rolde kullanıcı oluşturma yetkiniz yoktur.');
    }

    if ($creator->school_id !== $targetSchoolId) {
        return Response::deny('Kullanıcı sadece kendi okulunuza atanabilir.');
    }

    switch ($targetRole) {
        case 'teacher':
            return $creator->hasRole('manager')
                ? true
                : Response::deny('Öğretmen oluşturma yetkisi sadece yöneticilere aittir.');

        case 'student':
            return $creator->hasAnyRole(['manager', 'teacher'])
                ? true
                : Response::deny('Öğrenci oluşturma için yetkiniz yoktur.');

        case 'manager':
        case 'super-admin':
            return Response::deny('Bu yetki seviyesinde kullanıcı oluşturamazsınız.');
    }

    return Response::deny('Tanımlanmayan bir hata oluştu.');
}




    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }

}
