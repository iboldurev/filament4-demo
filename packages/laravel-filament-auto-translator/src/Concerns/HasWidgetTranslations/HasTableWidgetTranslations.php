<?php

namespace RalphJSmit\Filament\AutoTranslator\Concerns\HasWidgetTranslations;

use Illuminate\Contracts\Support\Htmlable;
use RalphJSmit\Filament\AutoTranslator\Concerns\HasWidgetTranslations;
use RalphJSmit\Filament\AutoTranslator\Filament\Widgets\TableWidget;

/**
 * @mixin TableWidget
 */
trait HasTableWidgetTranslations
{
    use HasWidgetTranslations;

    protected function getTableHeading(): string | Htmlable | null
    {
        return static::getTranslation('heading', allowNull: true) ?? parent::getTableHeading();
    }
}
