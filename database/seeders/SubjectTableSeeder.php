<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            [
                'subject_code' => 'AP',
                'subject_name' => 'Araling Panlipunan',
            ],
            [
                'subject_code' => 'ESP',
                'subject_name' => 'Edukasyon sa Pagpapakatao',
            ],
            [
                'subject_code' => 'ENGL',
                'subject_name' => 'English',
            ],
            [
                'subject_code' => 'FIL',
                'subject_name' => 'Filipino',
            ],
            [
                'subject_code' => 'MATH',
                'subject_name' => 'Mathematics',
            ],
            [
                'subject_code' => 'SCI',
                'subject_name' => 'Science',
            ],
            [
                'subject_code' => 'TLE',
                'subject_name' => 'Technology and Livelihood Education',
            ],
            [
                'subject_code' => 'MAPEH',
                'subject_name' => 'Music, Arts, Physical Education, and Health',
            ],
        ];

        foreach ($subjects as $subjectData) {
            Subject::updateOrCreate(
                ['subject_code' => $subjectData['subject_code']],
                array_merge($subjectData, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
