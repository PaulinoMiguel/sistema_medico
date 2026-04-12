<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if (! $user->isActive()) {
                Auth::logout();
                $message = match ($user->status) {
                    'passive' => 'Tu cuenta es de socio pasivo y no tiene acceso al sistema.',
                    'inactive' => 'Tu cuenta esta desactivada.',
                    default => 'Tu cuenta no tiene acceso al sistema.',
                };
                return back()->withErrors(['email' => $message]);
            }

            $request->session()->regenerate();

            // Auto-select first clinic
            $firstClinic = $user->clinics()->first();
            if ($firstClinic) {
                session(['active_clinic_id' => $firstClinic->id]);
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
