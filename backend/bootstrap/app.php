<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The SPA lives on a different origin and has no Laravel "login" page.
        // Send guests to the web root; API calls are converted to JSON 401 below.
        $middleware->redirectGuestsTo(fn () => '/');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // There is no Laravel "login" page in this SPA: unauthenticated API
        // requests must be a clean 401 JSON response, not a redirect to a missing route.
        $exceptions->respond(function (
            Response $response,
            Throwable $exception,
            Request $request,
        ) {
            if ($request->is('api/*') && $response->getStatusCode() === 302) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return $response;
        });
    })->create();
