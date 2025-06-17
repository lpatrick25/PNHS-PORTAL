<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdviserRequest;
use App\Http\Requests\UpdateAdviserRequest;
use App\Models\Adviser;
use App\Services\AdviserService;
use Illuminate\Http\Request;

class AdviserController extends Controller
{
    protected $adviserService;

    public function __construct(AdviserService $adviserService)
    {
        $this->adviserService = $adviserService;
    }

    public function index()
    {
        return response()->json($this->adviserService->index());
    }

    public function store(StoreAdviserRequest $request)
    {
        $data = $request->validated();
        return response()->json($this->adviserService->store($data));
    }

    public function update(UpdateAdviserRequest $request, $adviserId)
    {
        $data = $request->validated();
        return response()->json($this->adviserService->update($adviserId, $data));
    }

    public function destroy($adviserId)
    {
        return response()->json($this->adviserService->destroy($adviserId));
    }

    public function getByTeacherId($teacherId)
    {
        try {
            $adviser = Adviser::where('teacher_id', $teacherId)->first();
            return response()->json(['adviser' => $adviser]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch adviser details'], 500);
        }
    }
}
