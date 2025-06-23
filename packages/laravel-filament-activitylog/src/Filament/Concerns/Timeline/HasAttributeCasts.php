<?php

namespace RalphJSmit\Filament\Activitylog\Filament\Concerns\Timeline;

use Closure;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Models\Activity as ActivityModel;

trait HasAttributeCasts
{
    /**
     * @var array<string, array<string, Closure>>
     */
    protected array $attributeCasts = [];

    public function attributeCast(string $cast, Closure $formatUsing, string | array $subjectScopes = []): static
    {
        $subjectScopes = Arr::wrap($subjectScopes);

        if (! $subjectScopes) {
            $subjectScopes = ['default'];
        }

        foreach ($subjectScopes as $subjectScope) {
            $this->attributeCasts[$subjectScope][$cast] = $formatUsing;
        }

        return $this;
    }

    public function attributeCasts(array $attributeCasts, string | array $subjectScopes = []): static
    {
        $subjectScopes = Arr::wrap($subjectScopes);

        if (! $subjectScopes) {
            $subjectScopes = ['default'];
        }

        foreach ($subjectScopes as $subjectScope) {
            $this->attributeCasts[$subjectScope] = [
                ...$this->attributeCasts[$subjectScope] ?? [],
                ...$attributeCasts,
            ];
        }

        return $this;
    }

    public function getAttributeCastCallback(Activity | ActivityModel $activity, string $cast): ?Closure
    {
        if (str($cast)->contains(':')) {
            $cast = str($cast)->before(':');
        }

        $subjectType = $activity->subject_type ? (Relation::getMorphedModel($activity->subject_type) ?? $activity->subject_type) : null;

        return $subjectType && Arr::has($this->attributeCasts, "{$subjectType}.{$cast}")
            ? $this->attributeCasts[$subjectType][$cast]
            : $this->attributeCasts['default'][$cast] ?? null;
    }

    public function formatAttributeCast(Activity | ActivityModel $activity, string $cast, mixed $value): ?string
    {
        return $this->evaluate(
            value: $this->getAttributeCastCallback($activity, $cast),
            namedInjections: [
                'activity' => $activity,
                'event' => $activity->event,
                'value' => $value,
            ],
            typedInjections: [
                Activity::class => $activity,
                $activity::class => $activity,
            ],
        );
    }
}
