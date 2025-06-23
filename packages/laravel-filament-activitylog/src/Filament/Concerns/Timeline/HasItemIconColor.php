<?php

namespace RalphJSmit\Filament\Activitylog\Filament\Concerns\Timeline;

use Closure;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Models\Activity as ActivityModel;

trait HasItemIconColor
{
    /**
     * @var array<string, array<string, string|array|Closure>>
     */
    protected array $itemIconColors = [];

    public function itemIconColor(string $event, string | array | Closure | null $color, string | array $subjectScopes = []): static
    {
        $subjectScopes = Arr::wrap($subjectScopes);

        if (! $subjectScopes) {
            $subjectScopes = ['default'];
        }

        foreach ($subjectScopes as $subjectScope) {
            $this->itemIconColors[$subjectScope][$event] = $color;
        }

        return $this;
    }

    public function itemIconColors(array $colors, string | array $subjectScopes = []): static
    {
        $subjectScopes = Arr::wrap($subjectScopes);

        if (! $subjectScopes) {
            $subjectScopes = ['default'];
        }

        foreach ($subjectScopes as $subjectScope) {
            $this->itemIconColors[$subjectScope] = [
                ...$this->itemIconColors[$subjectScope] ?? [],
                ...$colors,
            ];
        }

        return $this;
    }

    public function getItemIconColor(Activity | ActivityModel $activity): null | string | array
    {
        $subjectType = $activity->subject_type ? (Relation::getMorphedModel($activity->subject_type) ?? $activity->subject_type) : null;

        $value = $subjectType && Arr::has($this->itemIconColors, "{$subjectType}.{$activity->event}")
            ? $this->itemIconColors[$subjectType][$activity->event]
            : (
                $subjectType && Arr::has($this->itemIconColors, "{$subjectType}.*")
                    ? $this->itemIconColors[$subjectType]['*']
                    : $this->itemIconColors['default'][$activity->event] ?? $this->itemIconColors['default']['*'] ?? null
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
