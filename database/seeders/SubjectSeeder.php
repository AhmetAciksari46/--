<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\Branch;
use App\Models\Grade;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        // Branch ve Grade verileri önceden seed edilmiş olmalı
        $branches = Branch::all();
        $grades = Grade::all();

        foreach ($branches as $branch) {
            foreach ($grades as $grade) {

                $name = $branch->name . ' - ' . $grade->name;

                Subject::updateOrCreate(
                    [
                        'name' => $name,
                        'branch_id' => $branch->id,
                        'grade_id' => $grade->id,
                    ],
                    [
                        'name' => $name,
                        'branch_id' => $branch->id,
                        'grade_id' => $grade->id,
                    ]
                );
            }
        }
    }
}
