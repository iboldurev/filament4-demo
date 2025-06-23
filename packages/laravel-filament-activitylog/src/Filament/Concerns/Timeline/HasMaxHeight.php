<?php

namespace RalphJSmit\Filament\Activitylog\Filament\Concerns\Timeline;

use Closure;

trait HasMaxHeight
{
    protected string | int | Closure | null $maxHeight = null;

    public function maxHeight(string | int | Closure | null $height = 500): static
    {
        $this->maxHeight = $height;

        return $this;
    }

    public function getMaxHeight(): string | int | null
    {
        return $this->evaluate($this->maxHeight);
    }
}
