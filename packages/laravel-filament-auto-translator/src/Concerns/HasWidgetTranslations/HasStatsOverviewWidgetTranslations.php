<?php

namespace RalphJSmit\Filament\AutoTranslator\Concerns\HasWidgetTranslations;

use RalphJSmit\Filament\AutoTranslator\Concerns\HasWidgetTranslations;
use RalphJSmit\Filament\AutoTranslator\Filament\Widgets\StatsOverviewWidget;

/**
 * @mixin StatsOverviewWidget
 */
trait HasStatsOverviewWidgetTranslations
{
    use HasWidgetTranslations;

    protected function getHeading(): ?string
    {
        return static::getTranslation('heading', allowNull: true) ?? parent::getHeading();
    }

    protected function getDescription(): ?string
    {
        return static::getTranslation('description', allowNull: true) ?? parent::getDescription();
    }
}
