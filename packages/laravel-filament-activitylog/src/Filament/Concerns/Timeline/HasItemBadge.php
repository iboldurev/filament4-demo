<?php

namespace RalphJSmit\Filament\Activitylog\Filament\Concerns\Timeline;

use Closure;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Models\Activity as ActivityModel;

trait HasItemBadge
{
    /**
     * @var array<string, array<string, string|Closure>>
     */
    protected array $itemBadges = [];

    public function itemBadge(string $event, string | Closure | null $badge, string | array $subjectScopes = []): static
    {
        $subjectScopes = Arr::wrap($subjectScopes);

        if (! $subjectScopes) {
            $subjectScopes = ['default'];
        }

        foreach ($subjectScopes as $subjectScope) {
            $this->itemBadges[$subjectScope][$event] = $badge;
        }

        return $this;
    }

    public function itemBadges(array $badges, string | array $subjectScopes = []): static
    {
        $subjectScopes = Arr::wrap($subjectScopes);

        if (! $subjectScopes) {
            $subjectScopes = ['default'];
        }

        foreach ($subjectScopes as $subjectScope) {
            $this->itemBadges[$subjectScope] = [
                ...$this->itemBadges[$subjectScope] ?? [],
                ...$badges,
            ];
        }

        return $this;
    }

    public function getItemBadge(Activity | ActivityModel $activity): null | string | array
    {
        $subjectType = $activity->subject_type ? (Relation::getMorphedModel($activity->subject_type) ?? $activity->subject_type) : null;

        $value = $subjectType && Arr::has($this->itemBadges, "{$subjectType}.{$activity->event}")
            ? $this->itemBadges[$subjectType][$activity->event]
            : (
                $subjectType && Arr::has($this->itemBadges, "{$subjectType}.*")
                    ? $this->itemBadges[$subjectType]['*']
                    : $this->itemBadges['default'][$activity->event] ?? $this->itemBadges['default']['*'] ?? null
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
