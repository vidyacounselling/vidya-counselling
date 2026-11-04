<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:254'],
            'password' => ['required', 'string', 'max:128'],
        ]);
        if (! Auth::attempt(['email' => mb_strtolower($data['email']), 'password' => $data['password'], 'is_admin' => true])) {
            throw ValidationException::withMessages(['email' => 'These credentials could not be verified.']);
        }
        $request->session()->regenerate();
        return redirect()->route('admin');
    }
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
