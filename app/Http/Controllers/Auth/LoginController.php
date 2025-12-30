<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // Check if admin
            if (Auth::user()->role === 'admin') {
                return redirect()->intended('/admin/dashboard');
            }

            // Customer tidak boleh login via web
            Auth::logout();
            return back()->withErrors([
                'email' => 'Akun customer hanya dapat login melalui aplikasi mobile'
            ]);
        }

        return back()->withErrors([
            'email' => 'Email atau password salah'
        ])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
