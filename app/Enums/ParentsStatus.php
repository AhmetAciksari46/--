<?php

namespace App\Enums;

enum ParentsStatus: string
{
    case TOGETHER_ALIVE = 'together_alive';
    case SEPARATE_ALIVE = 'separate_alive';
    case MOTHER_DECEASED = 'mother_deceased';
    case FATHER_DECEASED = 'father_deceased';
    case BOTH_DECEASED = 'both_deceased';

    public function label(): string
    {
        return match ($this) {
            self::TOGETHER_ALIVE => 'Anne baba sağ ve birlikte',
            self::SEPARATE_ALIVE => 'Anne baba sağ ama ayrı',
            self::MOTHER_DECEASED => 'Anne vefat',
            self::FATHER_DECEASED => 'Baba vefat',
            self::BOTH_DECEASED => 'Anne ve baba vefat',
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
