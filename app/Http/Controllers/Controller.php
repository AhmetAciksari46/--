<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function authorizeRole(array $roles)
{
    $user = auth()->user();

    if (!$user || !in_array($user->role, $roles)) {
        abort(403, 'Bu işlem için yetkiniz yok');
    }
}

}
