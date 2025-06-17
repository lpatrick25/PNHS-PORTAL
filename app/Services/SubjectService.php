<?php

namespace App\Services;

use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SubjectService
{
    public function index()
    {
        try {
            Log::info('Fetching all subjects for index');
            $subjects = Subject::all();

            $formattedSubjects = $subjects->map(function ($subject, $key) {
                $actions = '';
                $actions .= '<button type="button" class="btn btn-md btn-primary" title="Update" onclick="editSubject(' . $subject->id . ')"><i class="fa fa-edit"></i></button>';
                $actions .= '<button type="button" class="btn btn-md btn-danger ml-1" title="Delete" onclick="deleteSubject(' . $subject->id . ')"><i class="fa fa-trash"></i></button>';

                return [
                    'count' => $key + 1,
                    'subject_code' => $subject->subject_code,
                    'subject_name' => $subject->subject_name,
                    'action' => $actions,
                ];
            })->toArray();

            Log::info('Successfully fetched subjects', ['count' => count($formattedSubjects)]);
            return $formattedSubjects;
        } catch (Exception $e) {
            Log::error('Failed to fetch subjects', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    public function store(array $data)
    {
        try {
            Log::info('Attempting to store new subject', ['subject_code' => $data['subject_code']]);

            $subject = DB::transaction(function () use ($data) {
                return Subject::create($data);
            });

            Log::info('Subject stored successfully', ['subject_id' => $subject->id]);

            return [
                'valid' => true,
                'msg' => 'Subject added successfully.',
                'subject' => $subject,
            ];
        } catch (Exception $e) {
            Log::error('Failed to store subject', [
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to add subject. Please try again later.',
            ];
        }
    }

    public function update($subjectId, array $data)
    {
        try {
            Log::info('Attempting to update subject', ['subject_id' => $subjectId]);

            $subject = DB::transaction(function () use ($subjectId, $data) {
                $subject = Subject::findOrFail($subjectId);
                $subject->update($data);
                return $subject;
            });

            Log::info('Subject updated successfully', ['subject_id' => $subjectId]);

            return [
                'valid' => true,
                'msg' => 'Subject updated successfully.',
                'subject' => $subject,
            ];
        } catch (Exception $e) {
            Log::error('Failed to update subject', [
                'subject_id' => $subjectId,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to update subject. Please try again later.',
            ];
        }
    }

    public function destroy($subjectId)
    {
        try {
            Log::info('Attempting to delete subject', ['subject_id' => $subjectId]);

            DB::transaction(function () use ($subjectId) {
                $subject = Subject::findOrFail($subjectId);
                $subject->delete();
            });

            Log::info('Subject deleted successfully', ['subject_id' => $subjectId]);

            return [
                'valid' => true,
                'msg' => 'Subject deleted successfully.',
            ];
        } catch (Exception $e) {
            Log::error('Failed to delete subject', [
                'subject_id' => $subjectId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to delete subject: ' . $e->getMessage(),
            ];
        }
    }
}
