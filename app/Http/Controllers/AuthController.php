<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function show_loginForm()
    {
        if (Auth::check()) {
            return redirect()->route('admin');
        }

        return view('biu_auth.adm_login');
    }

    public function adm_login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            $user = User::where('username', $credentials['username'])->first();

            if (!$user) {
                return back()
                    ->withErrors(['credentials' => 'Invalid username or password.'])
                    ->withInput($request->except('password'));
            }

            if ($user->status === 'deactivate') {
                return back()
                    ->withErrors(['credentials' => 'Your account is deactivated. Please contact the administrator.'])
                    ->withInput($request->except('password'));
            }

            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();
                return redirect()->intended(route('dashboard'));
            }

            return back()
                ->withErrors(['credentials' => 'Invalid username or password.'])
                ->withInput($request->except('password'));

        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return back()->withErrors([
                'error' => 'An error occurred during login. Please try again.'
            ]);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('adm_login.form');
    }
}
