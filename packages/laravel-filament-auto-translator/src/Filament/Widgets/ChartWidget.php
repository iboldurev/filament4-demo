<?php

namespace RalphJSmit\Filament\AutoTranslator\Filament\Widgets;

use Filament\Widgets\ChartWidget as BaseWidget;
use RalphJSmit\Filament\AutoTranslator\Concerns\HasWidgetTranslations;
use RalphJSmit\Filament\AutoTranslator\Contracts\HasTranslations;

abstract class ChartWidget extends BaseWidget implements HasTranslations
{
    use HasWidgetTranslations\HasChartWidgetTranslations;
}
