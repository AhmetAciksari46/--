<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;
use App\Models\PackageWeekGradeRule;

class PackageWeekGradeRuleSeeder extends Seeder
{
    public function run(): void
    {
        $ruleMap = [
            'Standart Okul Paketi' => [
                ['grade' => 1, 'week_no' => 1, 'days_required' => 5],
                ['grade' => 1, 'week_no' => 2, 'days_required' => 5],
                ['grade' => 2, 'week_no' => 1, 'days_required' => 4],
                ['grade' => 2, 'week_no' => 2, 'days_required' => 4],
            ],
            'Öğrenci Premium Paketi' => [
                ['grade' => 8, 'week_no' => 1, 'days_required' => 6],
                ['grade' => 8, 'week_no' => 2, 'days_required' => 6],
                ['grade' => 12, 'week_no' => 1, 'days_required' => 6],
            ],
            'Hızlandırılmış Yaz Kampı' => [
                ['grade' => 7, 'week_no' => 1, 'days_required' => 3],
                ['grade' => 7, 'week_no' => 2, 'days_required' => 3],
            ],
        ];

        foreach ($ruleMap as $packageName => $rules) {
            $package = Package::where('name', $packageName)->first();

            if (! $package) {
                continue;
            }

            foreach ($rules as $rule) {
                PackageWeekGradeRule::updateOrCreate(
                    [
                        'package_id' => $package->id,
                        'grade' => $rule['grade'],
                        'week_no' => $rule['week_no'],
                    ],
                    ['days_required' => $rule['days_required']]
                );
            }
        }
    }
}
