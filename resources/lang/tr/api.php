<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Hata Mesajları
    |--------------------------------------------------------------------------
    | Bu dosya, Controller'larda kullanılan hata kodlarının (anahtarların)
    | karşılığı olan okunabilir hata mesajlarını merkezileştirir.
    |
    | Kullanım örneği: $this->errorResponse('user_not_found', 404);
    |
    */

    // GENEL HATALAR
    'not_found'             => 'Talep ettiğiniz kaynak bulunamadı.',
    'unauthorized'          => 'Bu işlemi gerçekleştirmek için yetkiniz yok.',
    'server_error'          => 'Sunucu tarafında beklenmedik bir hata oluştu.',
    'validation_failed'     => 'Gönderilen veriler doğrulama kuralına uymuyor.',
    'rate_limit'            => 'Çok fazla istek gönderdiniz. Lütfen daha sonra tekrar deneyin.',

    // KULLANICI / YETKİLENDİRME HATALARI
    'user_not_found'        => 'Belirtilen kullanıcı bulunamadı.',
    'invalid_credentials'   => 'Kullanıcı adı veya parola hatalı.',
    'token_expired'         => 'Erişim süreniz dolmuştur. Lütfen tekrar giriş yapın.',
    'token_invalid'         => 'Geçersiz erişim belirteci (token).',

    // KAYNAK (RESOURCE) HATALARI
    'resource_create_failed' => 'Yeni kayıt oluşturulamadı.',
    'resource_update_failed' => 'Kayıt güncellenemedi.',
    'resource_delete_failed' => 'Kayıt silinemedi.',
    'already_exists'        => 'Bu kayıt zaten mevcut.',
    'package_has_subscriptions' => 'Paketin aktif veya geçmiş abonelikleri olduğu için silinemez. Lütfen yalnızca pasif duruma getirin.',
    'profile_fetched' => 'Profil bilgileri başarıyla getirildi.',
    'profile_updated' => 'Profil başarıyla güncellendi.',
    'success' => 'İşlem başarılı.',
    'user_not_found' => 'Kullanıcı bulunamadı.',
    'unauthorized' => 'Bu işlem için yetkiniz yok.',
    'validation_failed' => 'Doğrulama hatası oluştu.',
    'unknown_error' => 'Bilinmeyen bir hata meydana geldi.',

    // Hata mesajları
    'validation_error' => 'Gönderilen veriler geçerli değil.',
    'unauthenticated' => 'Oturumunuz bulunamadı. Lütfen giriş yapın.',
    'unauthorized' => 'Bu işlem için yetkiniz yok.',
    'not_found' => 'Kayıt bulunamadı.',
    'route_not_found' => 'İstediğiniz adres bulunamadı.',
    'method_not_allowed' => 'Geçersiz istek yöntemi.',
    'http_error' => 'Bir HTTP hatası oluştu.',
    'unexpected_error' => 'Beklenmeyen bir hata oluştu. Lütfen tekrar deneyin.',
];
