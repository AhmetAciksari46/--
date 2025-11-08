<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Package;
use App\Models\PackageWeekSubjectRule;
use App\Models\Subject;

class PackageWeekSubjectRuleSeeder extends Seeder
{
    public function run(): void
    {
        $ruleMap = [
            'Standart Okul Paketi' => [
                ['grade' => 1, 'week_no' => 1, 'subject' => 'Matematik', 'hours' => 5],
                ['grade' => 1, 'week_no' => 1, 'subject' => 'Türkçe', 'hours' => 6],
                ['grade' => 1, 'week_no' => 2, 'subject' => 'Fen Bilimleri', 'hours' => 4],
                ['grade' => 2, 'week_no' => 1, 'subject' => 'İngilizce', 'hours' => 3],
            ],
            'Öğrenci Premium Paketi' => [
                ['grade' => 8, 'week_no' => 1, 'subject' => 'Matematik', 'hours' => 7],
                ['grade' => 8, 'week_no' => 1, 'subject' => 'Fen Bilimleri', 'hours' => 5],
                ['grade' => 12, 'week_no' => 1, 'subject' => 'Türkçe', 'hours' => 4],
            ],
            'Hızlandırılmış Yaz Kampı' => [
                ['grade' => 7, 'week_no' => 1, 'subject' => 'Beden Eğitimi', 'hours' => 2],
                ['grade' => 7, 'week_no' => 2, 'subject' => 'Sosyal Bilgiler', 'hours' => 3],
            ],
        ];

        foreach ($ruleMap as $packageName => $rules) {
            $package = Package::where('name', $packageName)->first();

            if (! $package) {
                continue;
            }

            foreach ($rules as $rule) {
                $subject = Subject::where('name', $rule['subject'])->first();

                if (! $subject) {
                    continue;
                }

                PackageWeekSubjectRule::updateOrCreate(
                    [
                        'package_id' => $package->id,
                        'grade' => $rule['grade'],
                        'week_no' => $rule['week_no'],
                        'subject_id' => $subject->id,
                    ],
                    ['hours' => $rule['hours']]
                );
            }
        }
    }
}
