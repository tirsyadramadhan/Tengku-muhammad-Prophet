<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Traits\ActivityLogger;
use App\Rules\ValidRecaptcha;

class AuthController extends Controller
{
    use ActivityLogger;
    public function showLogin()
    {
        // If the user is already logged in, send them back or to the dashboard
        if (Auth::check()) {
            return redirect()->intended('/dashboard');
        } else {
            return view('login');
        }
    }

    public function login(Request $request)
    {
        $rules = [
            'user_name'       => 'required|string|max:255',
            'password'        => 'required|string',
            'recaptcha_token' => ['required', new ValidRecaptcha()]
        ];

        $messages = [
            'user_name.required' => 'The name field is mandatory.',
            'user_name.max'      => 'Username terlalu panjang!',
            'password.required'  => 'Isi Password!'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors'  => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('user_name', 'password');
        $remember    = $request->input('remember_user') == '1'; // ← true/false from checkbox

        // ── Fix 1: Check if account is active before attempting login ──
        $user = \App\Models\User::where('user_name', $credentials['user_name'])->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Username tidak ditemukan',
                'errors'  => ['user_name' => ['Username tidak cocok dengan data kami']]
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Dinonaktifkan',
                'errors'  => ['user_name' => ['Akun kamu telah dinonaktifkan']]
            ], 403);
        }

        // ── Fix 2: Pass $remember into Auth::attempt() ──
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // ── Fix 3: Update last_login using primary key ──
            Auth::user()->update([
                'last_login' => now(),
            ]);

            $this->logLogin();

            return response()->json([
                'success'  => true,
                'message'  => 'Login Berhasil',
                'redirect' => '/dashboard'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Username / Password salah',
            'errors'  => ['user_name' => ['Username / Password yang dimasukkan salah']]
        ], 401);
    }

    public function logout(Request $request)
    {
        if ($request->isMethod('get')) {
            return redirect('/dashboard');
        }
        if ($request->isMethod('post')) {
            $this->logLogout();
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/');
        }
    }
}
