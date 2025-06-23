<?php

namespace App\Enums;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

enum UserStatus: string implements HasDescription, HasLabel
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';

    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::SUSPENDED => 'Suspended',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active user with full access',
            self::SUSPENDED => 'Suspended user with limited access',
        };
    }
}
