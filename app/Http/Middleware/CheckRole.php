<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Uso: ->middleware('role:admin,gerente')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        if (! $usuario) {
            return redirect()->route('login');
        }

        if ($usuario->isAdmin() || $usuario->hasAnyRole($roles)) {
            return $next($request);
        }

        abort(403, 'Seu perfil não tem acesso a esta área.');
    }
}
