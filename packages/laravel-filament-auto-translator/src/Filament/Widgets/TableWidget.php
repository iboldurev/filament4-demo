<?php

namespace RalphJSmit\Filament\AutoTranslator\Filament\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use RalphJSmit\Filament\AutoTranslator\Concerns\HasWidgetTranslations;
use RalphJSmit\Filament\AutoTranslator\Contracts\HasTranslations;

abstract class TableWidget extends BaseWidget implements HasTranslations
{
    use HasWidgetTranslations\HasTableWidgetTranslations;
}
