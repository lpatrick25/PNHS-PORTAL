<?php

namespace App\Exports;

use App\Models\ClassRecord;
use App\Models\TeacherSubjectLoad;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ClassRecordExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $subjectLoadId;

    public function __construct($subjectLoadId)
    {
        $this->subjectLoadId = $subjectLoadId;
    }

    public function collection()
    {
        return ClassRecord::with(['student', 'teacherSubjectLoad.subject', 'teacherSubjectLoad.teacher', 'schoolYear'])
            ->where('teacher_subject_load_id', $this->subjectLoadId)
            ->orderBy('quarter')
            ->orderByRaw("FIELD(records_type, 'Written Works', 'Performance Tasks', 'Quarterly Assessment')")
            ->get();
    }

    public function headings(): array
    {
        return [
            'Quarter',
            'Records Type',
            'Records Name',
            'Student Name',
            'Student Score',
            'Total Score',
            'Subject',
            'Grade Level',
            'Section',
            'Teacher',
            'School Year',
        ];
    }

    public function map($record): array
    {
        return [
            $record->quarter,
            $record->records_type,
            $record->records_name,
            $record->student->full_name ?? 'N/A',
            $record->student_score,
            $record->total_score ?? 'N/A',
            $record->teacherSubjectLoad->subject->subject_name ?? 'N/A',
            $record->teacherSubjectLoad->grade_level,
            $record->teacherSubjectLoad->section,
            $record->teacherSubjectLoad->teacher->full_name ?? 'N/A',
            $record->schoolYear->school_year ?? 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            'A' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
            'D' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
            'G' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
            'I' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
        ];
    }
}
