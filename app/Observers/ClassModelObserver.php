<?php

namespace App\Observers;

use App\Models\ClassModel;
use App\Models\Group;
use App\Enums\GroupType;
use App\Models\User;

class ClassModelObserver
{
    public function created(ClassModel $class)
    {
        // Sınıf oluşunca otomatik chat grubu oluştur
        $group = Group::create([
            'type' => GroupType::Classroom,
            'school_id' => $class->school_id,
            'class_model_id' => $class->id,
            'name' => "{$class->name} Sınıf Grubu",
            'created_by' => auth()->id() ?? 1,
        ]);

        // Öğretmeni ekle
        $group->members()->create([
            'user_id' => $class->teacher_id,
            'role_in_group' => 'teacher',
        ]);

        // Öğrencileri ekle
        foreach ($class->students as $student) {
            $group->members()->create([
                'user_id' => $student->id,
                'role_in_group' => 'student',
            ]);
        }

        // 4) Managerları ekle (explicit olarak)
        $managers = User::role('manager')
            ->whereHas('managerProfile', function ($q) use ($class) {
                $q->where('school_id', $class->school_id);
            })
            ->get();

        foreach ($managers as $manager) {
            $group->members()->create([
                'user_id' => $manager->id,
                'role_in_group' => 'manager',
            ]);
        }
    }

    /**
     * Sınıf silinmeden ÖNCE çalışır
     */
    public function deleting(ClassModel $class)
    {
        // Bu sınıfa ait classroom grubunu bul
        $group = Group::where('type', GroupType::Classroom)
            ->where('class_model_id', $class->id)
            ->first();

        if ($group) {
            // Grup üyeleri (opsiyonel ama temiz)
            $group->members()->delete();

            // Grup soft delete
            $group->delete();
        }
    }
}
