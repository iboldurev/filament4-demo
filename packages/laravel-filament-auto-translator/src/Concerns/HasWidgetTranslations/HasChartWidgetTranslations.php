<?php

namespace RalphJSmit\Filament\AutoTranslator\Concerns\HasWidgetTranslations;

use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use RalphJSmit\Filament\AutoTranslator\Concerns\HasWidgetTranslations;

/**
 * @mixin ChartWidget
 */
trait HasChartWidgetTranslations
{
    use HasWidgetTranslations;

    public function getHeading(): string | Htmlable | null
    {
        return static::getTranslation('heading', allowNull: true);
    }
}
