<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Adviser;
use App\Models\SchoolYear;
use App\Models\StudentStatus;

class StudentStatusTableSeeder extends Seeder
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

        // Retrieve adviser (from AdviserTableSeeder)
        $adviser = Adviser::whereHas('teacher', function ($query) {
            $query->where('email', 'amelia.deguzman@gmail.com');
        })->where('school_year_id', $schoolYear->id)
          ->where('grade_level', '7')
          ->where('section', 'Rose')
          ->first();

        if (!$adviser) {
            throw new \Exception('Adviser not found. Please seed the AdviserTableSeeder first.');
        }

        // Retrieve students (from StudentTableSeeder)
        $students = Student::whereIn('student_lrn', [
            '123456789101',
            '123456789102',
            '123456789103',
        ])->get()->keyBy('student_lrn');

        if ($students->isEmpty()) {
            throw new \Exception('No students found. Please seed the StudentTableSeeder first.');
        }

        $statuses = [
            [
                'student_lrn' => '123456789101',
                'adviser_id' => $adviser->id,
                'grade_level' => '7',
                'section' => 'Rose',
                'school_year_id' => $schoolYear->id,
            ],
            [
                'student_lrn' => '123456789102',
                'adviser_id' => $adviser->id,
                'grade_level' => '7',
                'section' => 'Rose',
                'school_year_id' => $schoolYear->id,
            ],
            [
                'student_lrn' => '123456789103',
                'adviser_id' => $adviser->id,
                'grade_level' => '7',
                'section' => 'Rose',
                'school_year_id' => $schoolYear->id,
            ],
        ];

        foreach ($statuses as $statusData) {
            $student = $students->get($statusData['student_lrn']);
            if (!$student) {
                continue; // Skip if student not found
            }

            StudentStatus::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'school_year_id' => $statusData['school_year_id'],
                    'grade_level' => $statusData['grade_level'],
                    'section' => $statusData['section'],
                ],
                [
                    'adviser_id' => $statusData['adviser_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
