<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Adviser;
use App\Models\Teacher;
use App\Models\SchoolYear;

class AdviserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure dependencies are seeded (Teacher and SchoolYear records must exist)
        $schoolYear = SchoolYear::firstOrCreate(
            ['school_year' => '2024-2025'],
            [
                'start_date' => '2024-06-01',
                'end_date' => '2025-03-31',
                'current' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Retrieve or create teachers (assuming TeacherTableSeeder has run)
        $teachers = Teacher::whereIn('email', [
            'amelia.deguzman@gmail.com',
            'benigno.lopez@gmail.com',
            'cecilia.garcia@gmail.com',
        ])->get()->keyBy('email');

        if ($teachers->isEmpty()) {
            throw new \Exception('No teachers found. Please seed the TeacherTableSeeder first.');
        }

        $advisers = [
            [
                'teacher_email' => 'amelia.deguzman@gmail.com',
                'grade_level' => '7',
                'section' => 'Rose',
                'school_year_id' => $schoolYear->id,
            ],
            [
                'teacher_email' => 'benigno.lopez@gmail.com',
                'grade_level' => '8',
                'section' => 'Jasmine',
                'school_year_id' => $schoolYear->id,
            ],
            [
                'teacher_email' => 'cecilia.garcia@gmail.com',
                'grade_level' => '9',
                'section' => 'Tulip',
                'school_year_id' => $schoolYear->id,
            ],
        ];

        foreach ($advisers as $adviserData) {
            $teacher = $teachers->get($adviserData['teacher_email']);
            if (!$teacher) {
                continue; // Skip if teacher not found
            }

            Adviser::updateOrCreate(
                [
                    'teacher_id' => $teacher->id,
                    'school_year_id' => $adviserData['school_year_id'],
                    'grade_level' => $adviserData['grade_level'],
                    'section' => $adviserData['section'],
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
