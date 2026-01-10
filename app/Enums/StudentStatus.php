<?php

namespace App\Enums;

enum StudentStatus: string
{
    case IN_PROGRESS = 'in_progress';
    case FORM_REQUEST = 'form_request';
    case SAVED = 'saved';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::IN_PROGRESS => 'Görüşülüyor',
            self::FORM_REQUEST => 'Form Talepleri',
            self::SAVED => 'Kaydedildi',
            self::CANCELLED => 'İptal edildi',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn($case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }
}
