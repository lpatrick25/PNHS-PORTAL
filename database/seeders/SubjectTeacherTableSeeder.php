<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\SchoolYear;
use App\Models\TeacherSubjectLoad;

class SubjectTeacherTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure dependencies exist
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

        // Retrieve teachers (from TeacherTableSeeder)
        $teachers = Teacher::whereIn('email', [
            'amelia.deguzman@gmail.com',
            'benigno.lopez@gmail.com',
            'cecilia.garcia@gmail.com',
        ])->get()->keyBy('email');

        if ($teachers->isEmpty()) {
            throw new \Exception('No teachers found. Please seed the TeacherTableSeeder first.');
        }

        // Retrieve subjects (from SubjectTableSeeder)
        $subjects = Subject::whereIn('subject_code', [
            'AP',
            'ESP',
            'ENGL',
        ])->get()->keyBy('subject_code');

        if ($subjects->isEmpty()) {
            throw new \Exception('No subjects found. Please seed the SubjectTableSeeder first.');
        }

        $subjectLoads = [
            [
                'teacher_email' => 'amelia.deguzman@gmail.com',
                'subject_code' => 'AP',
                'school_year_id' => $schoolYear->id,
                'grade_level' => '7',
                'section' => 'Rose',
            ],
            [
                'teacher_email' => 'benigno.lopez@gmail.com',
                'subject_code' => 'ESP',
                'school_year_id' => $schoolYear->id,
                'grade_level' => '7',
                'section' => 'Rose',
            ],
            [
                'teacher_email' => 'cecilia.garcia@gmail.com',
                'subject_code' => 'ENGL',
                'school_year_id' => $schoolYear->id,
                'grade_level' => '7',
                'section' => 'Rose',
            ],
        ];

        foreach ($subjectLoads as $loadData) {
            $teacher = $teachers->get($loadData['teacher_email']);
            $subject = $subjects->get($loadData['subject_code']);

            if (!$teacher || !$subject) {
                continue; // Skip if teacher or subject not found
            }

            TeacherSubjectLoad::updateOrCreate(
                [
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subject->id,
                    'school_year_id' => $loadData['school_year_id'],
                    'grade_level' => $loadData['grade_level'],
                    'section' => $loadData['section'],
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
