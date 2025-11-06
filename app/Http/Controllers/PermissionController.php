<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

/**
 * @OA\Tag(name="Permissions", description="List assignable permissions by role")
 */
class PermissionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/schools/{school}/permissions/teacher",
     *     summary="List permissions that a manager can assign to teachers",
     *     tags={"Permissions"},
     *     @OA\Response(response=200, description="List of assignable permissions")
     * )
     */
    public function teacherPermissions()
    {
        // Öğretmene verilemeyecek (yasaklı) izinler:
        $restricted = [
            'manager.create',
            'manager.delete',
            'manager.update',
            'school.create',
            'school.delete'
        ];

        $permissions = Permission::whereNotIn('name', $restricted)
            ->where(function ($q) {
                $q->where('name', 'like', 'student.%')
                    ->orWhere('name', 'like', 'teacher.%')
                    ->orWhere('name', 'like', 'profile.%');
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['permissions' => $permissions]);
    }

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/permissions/student",
     *     summary="List permissions that a manager can assign to students",
     *     tags={"Permissions"},
     *     @OA\Response(response=200, description="List of assignable permissions")
     * )
     */
    public function studentPermissions()
    {
        $restricted = [
            'manager.*',
            'teacher.*',
            'school.*'
        ];

        $permissions = Permission::whereNotIn('name', $restricted)
            ->where(function ($q) {
                $q->where('name', 'like', 'student.%')
                    ->orWhere('name', 'like', 'profile.%');
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['permissions' => $permissions]);
    }
}
