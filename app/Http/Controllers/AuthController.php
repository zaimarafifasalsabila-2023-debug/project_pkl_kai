<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Untuk demo, gunakan username "admin" dengan password "admin123"
        if ($credentials['username'] === 'admin' && $credentials['password'] === 'admin123') {
            session(['user' => ['username' => 'admin', 'name' => 'Administrator']]);
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'error' => 'Username atau password salah.',
        ])->withInput($request->except('password'));
    }

    public function logout(Request $request)
    {
        session()->forget('user');
        return redirect('/login');
    }
}
