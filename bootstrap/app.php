<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // API pública para la prueba técnica; proteger con autenticación antes de producción.
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ValidationException $e, Request $request) {
            if (!$request->is('api/*')) return null;
            return response()->json(['success'=>false,'message'=>'Verifica los datos enviados','errors'=>$e->errors()],422);
        });
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (!$request->is('api/*')) return null;
            $status = $e instanceof ModelNotFoundException ? 404
                : ($e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500);
            $message = match ($status) {
                401 => 'No autorizado',
                403 => 'Acceso denegado',
                404 => 'Recurso no encontrado',
                405 => 'Método no permitido',
                429 => 'Demasiadas solicitudes',
                default => $status >= 500 ? 'Error interno del servidor' : 'No se pudo completar la solicitud',
            };
            return response()->json(['success'=>false,'message'=>$message,'errors'=>new \stdClass()],$status);
        });
    })->create();
