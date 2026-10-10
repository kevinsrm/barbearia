<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Uso: ->middleware('permission:gerenciar-servicos')
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $usuario = $request->user();

        if (! $usuario) {
            return redirect()->route('login');
        }

        foreach ($permissions as $permission) {
            if (Gate::allows($permission)) {
                return $next($request);
            }
        }

        abort(403, 'Você não tem permissão para acessar esta área.');
    }
}
