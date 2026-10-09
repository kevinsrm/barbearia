<?php

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'guest' => RedirectIfAuthenticated::class,
            'role' => CheckRole::class,
            'permission' => CheckPermission::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));

        // Quem já está logado e tenta abrir /login volta para o lugar certo:
        // o painel para a equipe, o site para o cliente.
        $middleware->redirectUsersTo(fn () => auth()->user()?->destinoPosLogin() ?? route('site.index'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
