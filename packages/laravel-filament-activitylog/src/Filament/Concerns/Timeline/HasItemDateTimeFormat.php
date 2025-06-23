<?php

namespace RalphJSmit\Filament\Activitylog\Filament\Concerns\Timeline;

use Closure;

trait HasItemDateTimeFormat
{
    protected string | Closure | null $itemDateTimeFormat = null;

    public function itemDateTimeFormat(string | Closure $format): static
    {
        $this->itemDateTimeFormat = $format;

        return $this;
    }

    public function getItemDateTimeFormat(): ?string
    {
        return $this->evaluate($this->itemDateTimeFormat);
    }
}
