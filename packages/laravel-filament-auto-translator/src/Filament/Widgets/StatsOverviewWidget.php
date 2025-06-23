<?php

namespace RalphJSmit\Filament\AutoTranslator\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use RalphJSmit\Filament\AutoTranslator\Concerns\HasWidgetTranslations;
use RalphJSmit\Filament\AutoTranslator\Contracts\HasTranslations;

abstract class StatsOverviewWidget extends BaseWidget implements HasTranslations
{
    use HasWidgetTranslations\HasStatsOverviewWidgetTranslations;
}
