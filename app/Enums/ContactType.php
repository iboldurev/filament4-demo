<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ContactType: string implements HasLabel
{
    case MAIL = 'mail';
    case PHONE = 'phone';

    public function getLabel(): string
    {
        return match ($this) {
            self::MAIL => 'Email',
            self::PHONE => 'Phone',
        };
    }
}
