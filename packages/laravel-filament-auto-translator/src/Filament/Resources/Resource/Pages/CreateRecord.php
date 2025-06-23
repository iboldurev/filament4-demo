<?php

namespace RalphJSmit\Filament\AutoTranslator\Filament\Resources\Resource\Pages;

use Filament\Resources\Pages\CreateRecord as BasePage;
use RalphJSmit\Filament\AutoTranslator\Concerns\HasResourcePageTranslations;
use RalphJSmit\Filament\AutoTranslator\Contracts\HasTranslations;

class CreateRecord extends BasePage implements HasTranslations
{
    use HasResourcePageTranslations;
}
