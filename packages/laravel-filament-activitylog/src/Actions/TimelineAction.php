<?php

namespace RalphJSmit\Filament\Activitylog\Actions;

use Filament\Actions\Action;
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
