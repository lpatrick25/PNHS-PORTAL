<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class PasswordController extends Controller
{
    public function update(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[A-Z])(?=.*[0-9]).+$/',
                'confirmed'
            ],
        ], [
            'password.regex' => 'The password must contain at least one uppercase letter and one number.',
            'password.confirmed' => 'The password confirmation does not match.'
        ]);

        // Check if the authenticated user matches the ID
        if (Auth::id() != $id) {
            return response()->json([
                'valid' => false,
                'msg' => 'Unauthorized action.'
            ], 403);
        }

        try {
            // Update the user's password
            $user = User::findOrFail($id);
            $user->password = Hash::make($request->password);
            $user->save();

            $returnUrl = $this->getReturnUrlByRole($user->role);

            return response()->json([
                'valid' => true,
                'msg' => 'Password updated successfully.',
                'redirect_url' => $returnUrl
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'msg' => 'Error updating password: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function getReturnUrlByRole($role)
    {
        return match ($role) {
            'admin' => route('admin.dashboard'),
            'principal' => route('principal.dashboard'),
            'teacher' => route('teacher.dashboard'),
            'student' => route('student.dashboard'),
            default => route('home'),
        };
    }
}
