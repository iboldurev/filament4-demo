<?php

namespace RalphJSmit\Filament\Activitylog\Filament\Concerns\Timeline;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Models\Activity as ActivityModel;

trait HasCauser
{
    /**
     * @var array<string|null, string|Closure>
     */
    protected array $causerNames = [];

    protected null | Closure | string $causerUrl = null;

    public function causerName(?string $causerType, string | Closure $name): static
    {
        $this->causerNames[$causerType] = $name;

        return $this;
    }

    /**
     * @param  array<string|null, string|Closure>  $causerNames
     */
    public function causerNames(array $causerNames): static
    {
        $this->causerNames = [
            ...$this->causerNames,
            ...$causerNames,
        ];

        return $this;
    }

    public function hasCustomCauserNameCallback(?Model $causer): bool
    {
        return $causer
            ? isset($this->causerNames[$causer::class])
            : isset($this->causerNames[null]);
    }

    public function getCauserName(Activity | ActivityModel $activity, ?Model $causer): ?string
    {
        $value = $causer
            ? ($this->causerNames[$causer::class] ?? null)
            : ($this->causerNames[null] ?? null);

        return $this->evaluate(
            value: $value,
            namedInjections: [
                'activity' => $activity,
                'event' => $activity->event,
                'causer' => $causer,
            ],
            typedInjections: [
                Activity::class => $activity,
                $activity::class => $activity,
                ...[$causer ? [$causer::class => $causer] : []],
            ],
        );
    }

    public function causerUrl(null | Closure | string $url): static
    {
        $this->causerUrl = $url;

        return $this;
    }

    public function getCauserUrl(Activity | ActivityModel $activity, ?Model $causer): ?string
    {
        return $this->evaluate(
            value: $this->causerUrl,
            namedInjections: [
                'activity' => $activity,
                'event' => $activity->event,
                'causer' => $causer,
            ],
            typedInjections: [
                Activity::class => $activity,
                $activity::class => $activity,
                ...[$causer ? [$causer::class => $causer] : []],
            ],
        );
    }
}
