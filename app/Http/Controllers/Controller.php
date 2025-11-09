<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function authorizeRole(array $roles)
    {
        if (!auth()->check()) {
            abort(401, 'Giriş yapmalısınız.');
        }

        if (!in_array(auth()->user()->role, $roles)) {
            abort(403, 'Bu işlemi yapmaya yetkiniz yok.');
        }
    }
    protected function authorizeRoleOrPermission(array $roles = [], array $permissions = [])
    {
        $user = auth()->user();

        if (!$user) {
            abort(401, 'Yetkisiz erişim.');
        }

        // 1️⃣ Role sütunu (manuel sistem)
        if (!empty($roles) && in_array($user->role, $roles)) {
            return true;
        }

        // 2️⃣ Spatie hasRole() kontrolü (aktifse)
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($roles)) {
            return true;
        }

        // 3️⃣ Spatie permission kontrolü (aktifse)
        if (method_exists($user, 'hasAnyPermission') && $user->hasAnyPermission($permissions)) {
            return true;
        }

        // 4️⃣ Erişim yoksa hata
        abort(403, 'Bu işlem için yetkiniz yok.');
    }
}
