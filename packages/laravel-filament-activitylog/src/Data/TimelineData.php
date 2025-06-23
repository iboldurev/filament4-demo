<?php

namespace RalphJSmit\Filament\Activitylog\Data;

use Closure;
use Illuminate\Support\Collection;
use RalphJSmit\Filament\Activitylog\Filament\Concerns\Timeline\ActivityTimelineItem;

class TimelineData
{
    public function __construct(
        protected Collection $activityTimelineItems,
        protected Closure $evaluationCallback,
        protected null | string | Closure $emptyStateHeading = null,
        protected null | string | Closure $emptyStateDescription = null,
        protected null | string | Closure $emptyStateIcon = null,
        protected bool | Closure $isCompact = false,
        protected bool | Closure $isSearchable = false,
        protected null | string | int | Closure $maxHeight = null,
        protected null | string | Closure $modelLabel = null,
    ) {}

    /**
     * @return Collection<array-key, ActivityTimelineItem>
     */
    public function getActivityTimelineItems(): Collection
    {
        return $this->activityTimelineItems;
    }

    public function getEmptyStateHeading(): string
    {
        return ($this->evaluationCallback)($this->emptyStateHeading) ?? __('filament-activitylog::translations.data.timeline-configuration-data.empty-state-heading');
    }

    public function getEmptyStateDescription(): ?string
    {
        if ($this->emptyStateDescription) {
            return ($this->evaluationCallback)($this->emptyStateDescription);
        }

        if ($this->modelLabel) {
            return __('filament-activitylog::translations.data.timeline-configuration-data.empty-state-description', [
                'modelLabel' => ($this->evaluationCallback)($this->modelLabel),
            ]);
        }

        return null;
    }

    public function getEmptyStateIcon(): string
    {
        return ($this->evaluationCallback)($this->emptyStateIcon) ?? 'heroicon-o-x-mark';
    }

    public function isCompact(): bool
    {
        return ($this->evaluationCallback)($this->isCompact);
    }

    public function isSearchable(): bool
    {
        return ($this->evaluationCallback)($this->isSearchable);
    }

    public function getMaxHeight(): string | int | null
    {
        return ($this->evaluationCallback)($this->maxHeight);
    }
}
