<?php

namespace RalphJSmit\Filament\Activitylog\Filament\Concerns\Timeline;

use Closure;

trait HasItemDateTimeTimezone
{
    protected string | Closure | null $itemDateTimeTimezone = null;

    public function itemDateTimeTimezone(string | Closure $timezone): static
    {
        $this->itemDateTimeTimezone = $timezone;

        return $this;
    }

    public function getItemDateTimeTimezone(): ?string
    {
        return $this->evaluate($this->itemDateTimeTimezone);
    }
}
