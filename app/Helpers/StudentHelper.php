<?php

use App\Models\Student;

if (!function_exists('getStudentsNotEnrolledInSchoolYear')) {
    function getStudentsNotEnrolledInSchoolYear($schoolYearId)
    {
        return Student::whereDoesntHave('statuses', function ($query) use ($schoolYearId) {
            $query->where('school_year_id', $schoolYearId);
        })->get();
    }
}

if (!function_exists('getStudentsEnrolledInSchoolYear')) {
    function getStudentsEnrolledInSchoolYear($schoolYearId)
    {
        return Student::whereHas('statuses', function ($query) use ($schoolYearId) {
            $query->where('school_year_id', $schoolYearId);
        })->get();
    }
}

if (!function_exists('getStudentByLRN')) {
    function getStudentByLRN($lrn)
    {
        return Student::where('lrn', $lrn)->first();
    }
}

if (!function_exists('getStudentsByAdviserId')) {
    function getStudentsByAdviserId($adviserId)
    {
        return Student::whereHas('statuses', function ($query) use ($adviserId) {
            $query->where('adviser_id', $adviserId);
        })->get();
    }
}
