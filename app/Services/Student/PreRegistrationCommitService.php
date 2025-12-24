<?php

namespace App\Services\Student;

use App\Models\StudentPreRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PreRegistrationCommitService
{
    public function commit(StudentPreRegistration $preReg, array $extraData)
    {
        DB::transaction(function () use ($preReg, $extraData) {

            // 1️⃣ User oluştur (student)
            $user = User::create([
                'name' => $preReg->student_name . ' ' . $preReg->student_surname,
                'userName' => Str::slug($preReg->student_name) . rand(1000, 9999),
                'password' => bcrypt(Str::random(10)),
                'role' => 'student',
            ]);

            $user->assignRole('student');

            // 2️⃣ Student profile
            $user->studentProfile()->create([
                'school_id' => $preReg->school_id,
                'classroom_id' => $extraData['classroom_id'],
                'birth_date' => $preReg->birth_date,
                'gender' => $preReg->gender,
                'address' => $preReg->address,
            ]);

            // 3️⃣ Parents
            if ($preReg->mother) {
                $user->parents()->create([
                    'type' => 'mother',
                    ...$preReg->mother
                ]);
            }

            if ($preReg->father) {
                $user->parents()->create([
                    'type' => 'father',
                    ...$preReg->father
                ]);
            }

            // 4️⃣ Status güncelle
            $preReg->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);
        });
    }
}
