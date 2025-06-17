<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePrincipalRequest;
use App\Http\Requests\UpdatePrincipalRequest;
use App\Services\PrincipalService;
use Illuminate\Http\Request;

class PrincipalController extends Controller
{
    protected $principalService;

    public function __construct(PrincipalService $principalService)
    {
        $this->principalService = $principalService;
    }

    public function index()
    {
        return response()->json($this->principalService->index());
    }

    public function store(StorePrincipalRequest $request)
    {
        $data = $request->validated();
        $response = $this->principalService->store($data);
        return response()->json($response);
    }

    public function update(UpdatePrincipalRequest $request, $principalId)
    {
        $data = $request->except('_token');
        $response = $this->principalService->update($principalId, $data);
        return response()->json($response);
    }

    public function updateAvatar(Request $request, $principalId)
    {
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $response = $this->principalService->updateAvatar($principalId, $file);
            return response()->json($response);
        }
        return response()->json(['valid' => false, 'msg' => 'No file uploaded.']);
    }
}
