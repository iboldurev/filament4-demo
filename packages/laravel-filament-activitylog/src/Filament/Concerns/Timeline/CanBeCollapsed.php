<?php

namespace RalphJSmit\Filament\Activitylog\Filament\Concerns\Timeline;

use Closure;

trait CanBeCollapsed
{
    protected bool | Closure | null $isCollapsible = null;

    public function collapsible(bool | Closure | null $condition = true): static
    {
        $this->isCollapsible = $condition;

        return $this;
    }

    public function isCollapsible(): bool
    {
        return (bool) ($this->evaluate($this->isCollapsible) ?? false);
    }
}
