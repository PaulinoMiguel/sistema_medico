<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user has at least one clinic associated.
 *
 * Without a clinic, operational features (patients, appointments, etc.)
 * cannot function correctly because they all rely on session('active_clinic_id').
 *
 * Redirects the user to create their first clinic if none exists. The
 * /clinics/* routes themselves are NOT gated by this middleware (otherwise
 * the user would be in an infinite redirect loop).
 *
 * Apply to operational route groups, NOT to dashboard, profile, clinics,
 * logout, or any auth routes.
 */
class EnsureClinicExists
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only doctors are subject to this guard. Secretaries are always
        // attached to a clinic when created (no orphan secretaries possible).
        if ($user && $user->isDoctor() && $user->clinics()->count() === 0) {
            return redirect()->route('dashboard')
                ->with('warning', 'No tienes clinicas asignadas. Contacta al administrador.');
        }

        return $next($request);
    }
}
