<?php

namespace RalphJSmit\Filament\Activitylog\Tables\Actions;

use Filament\Tables\Actions\Action;
use RalphJSmit\Filament\Activitylog\Filament\Concerns\ConfigureTimelineAction;

class TimelineAction extends Action
{
    use ConfigureTimelineAction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configureTimelineAction();
    }
}
