<?php
namespace App\Traits;

use Illuminate\Http\JsonResponse;

/**
 * API yanıtlarını standartlaştırmak için kullanılan Trait.
 * Controller'larda "use ApiResponser;" şeklinde dahil edilir.
 */
trait ApiResponser
{
    /**
     * Hata mesajını çeviri (lang) dosyasından çeker.
     * Mesajlar lang/tr/api.php dosyasında tanımlanmalıdır.
     *
     * @param string $key Hata mesajı anahtarı (örn: 'user_not_found')
     * @return string
     */
    protected function getErrorMessage(string $key): string
    {
        // 'api.' öneki ile lang/tr/api.php dosyasından anahtarı çek.
        $message = __('api.' . $key);

        // Eğer çeviri bulunamazsa (anahtarın kendisi dönerse), bir uyarı mesajı döndür.
        if ($message === 'api.' . $key) {
             return "Bilinmeyen hata mesajı anahtarı: {$key}";
        }

        return $message;
    }

    /**
     * Başarılı (success) yanıt için genel yapı.
     *
     * @param mixed $data Yanıt verisi (model, koleksiyon, array vb.)
     * @param string|null $message Başarı mesajı (isteğe bağlı)
     * @param int $code HTTP durum kodu (varsayılan 200 OK)
     * @return JsonResponse
     */
    protected function successResponse($data, ?string $message = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Hatalı (error) yanıt için genel yapı.
     *
     * @param string $key Hata mesajı anahtarı (lang/tr/api.php'den çekilir)
     * @param int $code HTTP durum kodu (varsayılan 400 Bad Request)
     * @return JsonResponse
     */
    protected function errorResponse(string $key, int $code = 400): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $this->getErrorMessage($key), // Merkezileştirilmiş hatayı çek
            'data' => null,
        ], $code);
    }
}