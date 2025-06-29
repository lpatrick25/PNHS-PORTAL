<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Handle default password
            if ($credentials['username'] === $credentials['password']) {
                session(['user_id' => $user->id]);
                return response()->json([
                    'valid' => true,
                    'msg' => 'Default password detected. Please change your password.',
                    'return_url' => route('password.change')
                ]);
            }

            $returnUrl = $this->getReturnUrlByRole($user->role);
            return response()->json(['valid' => true, 'msg' => 'Login successful', 'return_url' => $returnUrl]);
        }

        return response()->json(['valid' => false, 'msg' => 'Invalid credentials'], 200);
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

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login')->with('status', 'Logged out successfully.');
    }

    // Show change password form
    public function showChangePasswordForm()
    {
        return view('change-password');
    }
}
