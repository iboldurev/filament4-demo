<?php

namespace RalphJSmit\Filament\AutoTranslator\Contracts;

use Countable;
use RalphJSmit\Filament\AutoTranslator\Enums\PageTranslationContext;

interface HasTranslations
{
    public static function getTranslation(string $key, array $replace = [], Countable | float | int | null $number = null, bool $allowNull = false, ?PageTranslationContext $pageTranslationContext = null, ?string $pageTranslationContextKey = null): mixed;
}
