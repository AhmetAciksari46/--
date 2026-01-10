<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponser
{
    /**
     * Lang dosyasından hata mesajı çeker.
     */
    protected function getErrorMessage(string $key): string
    {
        $message = __('api.' . $key);

        return $message === 'api.' . $key
            ? "{$key}"
            : $message;
    }

    /**
     * Başarılı (success) yanıt.
     */
    protected function successResponse($data = null, ?string $message = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => $message ?? __('api.success'),
            'data'    => $data ?? (object)[],
            'code' => 200,
        ], $code);
    }

    /**
     * Hatalı (error) yanıt.
     */
    protected function errorResponse(string $key, int $code = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'message' => $this->getErrorMessage($key),
            'errors'  => $errors,
            'data'    => null,
            "code" => $code,
        ], 200);
    }
}
