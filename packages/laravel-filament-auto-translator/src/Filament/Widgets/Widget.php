<?php

namespace RalphJSmit\Filament\AutoTranslator\Filament\Widgets;

use Filament\Widgets\Widget as BaseWidget;
use RalphJSmit\Filament\AutoTranslator\Concerns\HasWidgetTranslations;
use RalphJSmit\Filament\AutoTranslator\Contracts\HasTranslations;

abstract class Widget extends BaseWidget implements HasTranslations
{
    use HasWidgetTranslations;
}
