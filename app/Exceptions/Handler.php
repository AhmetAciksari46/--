<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use App\Traits\ApiResponser;

class Handler extends ExceptionHandler
{
    use ApiResponser;

    /**
     * Bir istisnayı HTTP yanıtına dönüştür.
     */
    public function render($request, Throwable $e)
    {
        // Sadece API isteklerinde özel JSON formatında dön
        if (
            $request->expectsJson() ||
            $request->is('api/*') ||
            $request->wantsJson()
        ) {

            // 🔐 Doğrulama hatası
            if ($e instanceof ValidationException) {
                return $this->errorResponse('validation_error', 422, $e->errors());
            }

            // 🔒 Kimlik doğrulama hatası
            if ($e instanceof AuthenticationException) {
                return $this->errorResponse('unauthenticated', 401);
            }

            // 🚫 Yetkisiz işlem hatası
            if ($e instanceof HttpException && $e->getStatusCode() === 403) {
                return $this->errorResponse('unauthorized', 403);
            }

            // 🕵️‍♂️ Model bulunamadı (örneğin firstOrFail)
            if ($e instanceof ModelNotFoundException) {
                return $this->errorResponse('not_found', 404);
            }

            // 📭 Route bulunamadı
            if ($e instanceof NotFoundHttpException) {
                return $this->errorResponse('route_not_found', 404);
            }

            // 🚫 HTTP metodu hatası
            if ($e instanceof MethodNotAllowedHttpException) {
                return $this->errorResponse('method_not_allowed', 405);
            }

            // 💾 Genel HTTP hatası
            if ($e instanceof HttpException) {
                return $this->errorResponse('http_error', $e->getStatusCode());
            }

            // 💥 Tüm beklenmeyen hatalar
            return $this->errorResponse('unexpected_error', 500, [
                'error' => config('app.debug') ? $e->getMessage() : null,
            ]);
        }

        // API değilse varsayılan davranış
        return parent::render($request, $e);
    }
}
