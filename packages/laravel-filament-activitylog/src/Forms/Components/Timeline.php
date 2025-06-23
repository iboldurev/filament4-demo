<?php

namespace RalphJSmit\Filament\Activitylog\Forms\Components;

use Filament\Forms;
use RalphJSmit\Filament\Activitylog;

class Timeline extends Forms\Components\Field
{
    use Activitylog\Filament\Concerns\Timeline;

    protected string $view = 'filament-activitylog::forms.components.timeline';

    public static function make(string $name = 'activities'): static
    {
        return parent::make($name);
    }

    protected function setUp(): void
    {
        $this
            ->dehydrated(false)
            ->hidden(function (string $context, self $component) {
                if ($context === 'create') {
                    return true;
                }

                return false;
            });
    }
}
