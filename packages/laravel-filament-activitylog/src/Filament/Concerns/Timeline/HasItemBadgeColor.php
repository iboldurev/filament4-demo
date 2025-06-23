<?php

namespace RalphJSmit\Filament\Activitylog\Filament\Concerns\Timeline;

use Closure;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Models\Activity as ActivityModel;

trait HasItemBadgeColor
{
    /**
     * @var array<string, array<string, string|array|Closure>>
     */
    protected array $itemBadgeColors = [];

    public function itemBadgeColor(string $event, string | array | Closure | null $color, string | array $subjectScopes = []): static
    {
        $subjectScopes = Arr::wrap($subjectScopes);

        if (! $subjectScopes) {
            $subjectScopes = ['default'];
        }

        foreach ($subjectScopes as $subjectScope) {
            $this->itemBadgeColors[$subjectScope][$event] = $color;
        }

        return $this;
    }

    public function itemBadgeColors(array $colors, string | array $subjectScopes = []): static
    {
        $subjectScopes = Arr::wrap($subjectScopes);

        if (! $subjectScopes) {
            $subjectScopes = ['default'];
        }

        foreach ($subjectScopes as $subjectScope) {
            $this->itemBadgeColors[$subjectScope] = [
                ...$this->itemBadgeColors[$subjectScope] ?? [],
                ...$colors,
            ];
        }

        return $this;
    }

    public function getItemBadgeColor(Activity | ActivityModel $activity): null | string | array
    {
        $subjectType = $activity->subject_type ? (Relation::getMorphedModel($activity->subject_type) ?? $activity->subject_type) : null;

        $value = $subjectType && Arr::has($this->itemBadgeColors, "{$subjectType}.{$activity->event}")
            ? $this->itemBadgeColors[$subjectType][$activity->event]
            : (
                $subjectType && Arr::has($this->itemBadgeColors, "{$subjectType}.*")
                    ? $this->itemBadgeColors[$subjectType]['*']
                    : $this->itemBadgeColors['default'][$activity->event] ?? $this->itemBadgeColors['default']['*'] ?? null
            );

        return $this->evaluate(
            value: $value,
            namedInjections: [
                'activity' => $activity,
                'event' => $activity->event,
            ],
            typedInjections: [
                Activity::class => $activity,
                $activity::class => $activity,
            ],
        );
    }
}
