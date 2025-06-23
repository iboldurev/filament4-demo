<?php

namespace RalphJSmit\Filament\Activitylog\Filament\Concerns\Timeline;

use Closure;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Models\Activity as ActivityModel;

trait HasItemIcon
{
    /**
     * @var array<string, array<string, string|Closure>>
     */
    protected array $itemIcons = [];

    public function itemIcon(string $event, string | Closure | null $icon, string | array $subjectScopes = []): static
    {
        $subjectScopes = Arr::wrap($subjectScopes);

        if (! $subjectScopes) {
            $subjectScopes = ['default'];
        }

        foreach ($subjectScopes as $subjectScope) {
            $this->itemIcons[$subjectScope][$event] = $icon;
        }

        return $this;
    }

    public function itemIcons(array $icons, string | array $subjectScopes = []): static
    {
        $subjectScopes = Arr::wrap($subjectScopes);

        if (! $subjectScopes) {
            $subjectScopes = ['default'];
        }

        foreach ($subjectScopes as $subjectScope) {
            $this->itemIcons[$subjectScope] = [
                ...$this->itemIcons[$subjectScope] ?? [],
                ...$icons,
            ];
        }

        return $this;
    }

    public function getItemIcon(Activity | ActivityModel $activity): ?string
    {
        $subjectType = $activity->subject_type ? (Relation::getMorphedModel($activity->subject_type) ?? $activity->subject_type) : null;

        $value = $subjectType && Arr::has($this->itemIcons, "{$subjectType}.{$activity->event}")
            ? $this->itemIcons[$subjectType][$activity->event]
            : (
                $subjectType && Arr::has($this->itemIcons, "{$subjectType}.*")
                        ? $this->itemIcons[$subjectType]['*']
                        : $this->itemIcons['default'][$activity->event] ?? $this->itemIcons['default']['*'] ?? null
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
