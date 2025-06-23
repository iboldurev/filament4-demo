<?php

namespace RalphJSmit\Filament\Activitylog\Filament\Concerns\Timeline;

use Closure;
use Illuminate\Contracts\Support\Htmlable;

trait HasEmptyState
{
    protected string | Closure | null $emptyStateIcon = null;

    protected string | Htmlable | Closure | null $emptyStateHeading = null;

    protected string | Htmlable | Closure | null $emptyStateDescription = null;

    public function emptyStateIcon(string | Closure | null $icon): static
    {
        $this->emptyStateIcon = $icon;

        return $this;
    }

    public function emptyStateHeading(string | Htmlable | Closure | null $heading): static
    {
        $this->emptyStateHeading = $heading;

        return $this;
    }

    public function emptyStateDescription(string | Htmlable | Closure | null $description): static
    {
        $this->emptyStateDescription = $description;

        return $this;
    }

    public function getEmptyStateIcon(): ?string
    {
        return $this->evaluate($this->emptyStateIcon);
    }

    public function getEmptyStateHeading(): ?string
    {
        return $this->evaluate($this->emptyStateHeading);
    }

    public function getEmptyStateDescription(): ?string
    {
        return $this->evaluate($this->emptyStateDescription);
    }
}
