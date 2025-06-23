<?php

namespace RalphJSmit\Filament\Activitylog\Infolists\Components;

use Filament\Infolists;
use RalphJSmit\Filament\Activitylog;

class Timeline extends Infolists\Components\Entry
{
    use Activitylog\Filament\Concerns\Timeline;

    protected string $view = 'filament-activitylog::infolists.components.timeline';

    public static function make(string $name = 'activities'): static
    {
        return parent::make($name);
    }
}
