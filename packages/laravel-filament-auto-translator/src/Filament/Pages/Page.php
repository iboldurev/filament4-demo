<?php

namespace RalphJSmit\Filament\AutoTranslator\Filament\Pages;

use Filament\Pages\Page as BasePage;
use RalphJSmit\Filament\AutoTranslator\Concerns\HasPageTranslations;
use RalphJSmit\Filament\AutoTranslator\Contracts\HasTranslations;

abstract class Page extends BasePage implements HasTranslations
{
    use HasPageTranslations;
}
