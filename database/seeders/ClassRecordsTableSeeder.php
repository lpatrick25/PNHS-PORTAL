<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\SchoolYear;
use App\Models\TeacherSubjectLoad;
use App\Models\ClassRecord;

class ClassRecordsTableSeeder extends Seeder
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

        // Retrieve students (from StudentTableSeeder)
        $students = Student::whereIn('student_lrn', [
            '123456789101',
            '123456789102',
            '123456789103',
        ])->get()->keyBy('student_lrn');

        if ($students->isEmpty()) {
            throw new \Exception('No students found. Please seed the StudentTableSeeder first.');
        }

        // Retrieve teacher subject loads (from SubjectTeacherTableSeeder)
        $subjectLoads = TeacherSubjectLoad::whereHas('schoolYear', function ($query) {
            $query->where('school_year', '2024-2025');
        })->where('grade_level', '7')
          ->where('section', 'Rose')
          ->whereHas('subject', function ($query) {
              $query->whereIn('subject_code', ['AP', 'ESP', 'ENGL']);
          })->get()->keyBy('subject.subject_code');

        if ($subjectLoads->isEmpty()) {
            throw new \Exception('No teacher subject loads found. Please seed the SubjectTeacherTableSeeder first.');
        }

        $quarterKeys = [
            '1st Quarter',
            '2nd Quarter',
            '3rd Quarter',
            '4th Quarter',
        ];

        $totalScoreWrittenWorks = 30;
        $totalScorePerformanceTasks = 30;
        $totalScoreQuarterlyAssessment = 50;
        $writtenWorksCount = 10;
        $performanceTasksCount = 10;

        foreach ($students as $student) {
            foreach ($quarterKeys as $quarterName) {
                foreach ($subjectLoads as $subjectLoad) {
                    // Insert Written Works
                    for ($i = 1; $i <= $writtenWorksCount; $i++) {
                        ClassRecord::updateOrCreate(
                            [
                                'teacher_subject_load_id' => $subjectLoad->id,
                                'student_id' => $student->id,
                                'records_name' => "Written Work $i",
                                'quarter' => $quarterName,
                                'school_year_id' => $schoolYear->id,
                            ],
                            [
                                'records_type' => 'Written Works',
                                'total_score' => $totalScoreWrittenWorks,
                                'student_score' => rand(15, 30),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                    }

                    // Insert Performance Tasks
                    for ($i = 1; $i <= $performanceTasksCount; $i++) {
                        ClassRecord::updateOrCreate(
                            [
                                'teacher_subject_load_id' => $subjectLoad->id,
                                'student_id' => $student->id,
                                'records_name' => "Performance Task $i",
                                'quarter' => $quarterName,
                                'school_year_id' => $schoolYear->id,
                            ],
                            [
                                'records_type' => 'Performance Tasks',
                                'total_score' => $totalScorePerformanceTasks,
                                'student_score' => rand(15, 30),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                    }

                    // Insert Quarterly Assessment
                    ClassRecord::updateOrCreate(
                        [
                            'teacher_subject_load_id' => $subjectLoad->id,
                            'student_id' => $student->id,
                            'records_name' => "$quarterName Assessment",
                            'quarter' => $quarterName,
                            'school_year_id' => $schoolYear->id,
                        ],
                        [
                            'records_type' => 'Quarterly Assessment',
                            'total_score' => $totalScoreQuarterlyAssessment,
                            'student_score' => rand(40, 50),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }
    }
}
