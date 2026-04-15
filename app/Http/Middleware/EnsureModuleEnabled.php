<?php

namespace App\Http\Middleware;

use App\Models\InstallationSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleEnabled
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        if (! InstallationSetting::current()->moduleEnabled($module)) {
            abort(404, "Modulo '{$module}' deshabilitado en esta instalacion.");
        }

        return $next($request);
    }
}
