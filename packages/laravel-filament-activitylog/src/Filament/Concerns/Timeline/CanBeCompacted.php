<?php

namespace RalphJSmit\Filament\Activitylog\Filament\Concerns\Timeline;

use Closure;

trait CanBeCompacted
{
    protected bool | Closure $isCompact = false;

    protected bool | Closure $shouldConvertHeroicons = true;

    public function compact(bool | Closure $condition = true): static
    {
        $this->isCompact = $condition;

        return $this;
    }

    public function convertHeroicons(bool | Closure $condition = true): static
    {
        $this->shouldConvertHeroicons = $condition;

        return $this;
    }

    public function isCompact(): bool
    {
        return (bool) $this->evaluate($this->isCompact);
    }

    public function shouldConvertHeroicons(): bool
    {
        return (bool) $this->evaluate($this->shouldConvertHeroicons);
    }
}
