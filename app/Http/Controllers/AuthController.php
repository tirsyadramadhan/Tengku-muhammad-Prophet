<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuthController extends Controller
{
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
        if (Auth::check()) {
            return redirect('/dashboard');
        } else {
            if ($request->isMethod('get')) {
                return redirect('/');
            } else {
                $credentials = $request->validate([
                    'user_name' => 'required|string',
                    'password' => 'required|string',
                ]);

                // Laravel's Auth::attempt expects an array of credentials
                if (Auth::attempt($credentials)) {
                    $request->session()->regenerate();

            // Update the last_login column using Carbon
                    /** @var \App\Models\User $user */
                    $user = Auth::user();
                    $user->last_login = Carbon::now();
                    $user->save();

                    return redirect()->intended('/dashboard');
                }

                return back()->withErrors([
                    'user_name' => 'The provided credentials do not match our records.',
                ])->onlyInput('user_name');
            }
        }
    }

    public function logout(Request $request)
    {
        if ($request->isMethod('get')) {
            return redirect('/dashboard');
        }
        if ($request->isMethod('post')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/');
        }
    }
}
