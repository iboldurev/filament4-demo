<?php

namespace RalphJSmit\Filament\AutoTranslator\Filament\Resources;

use Filament\Resources\Resource as BaseResource;
use RalphJSmit\Filament\AutoTranslator\Concerns\HasResourceTranslations;
use RalphJSmit\Filament\AutoTranslator\Contracts\HasTranslations;

class Resource extends BaseResource implements HasTranslations
{
    use HasResourceTranslations;
}
