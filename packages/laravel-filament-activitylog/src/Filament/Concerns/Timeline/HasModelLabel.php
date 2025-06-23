<?php

namespace RalphJSmit\Filament\Activitylog\Filament\Concerns\Timeline;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Models\Activity as ActivityModel;

trait HasModelLabel
{
    /**
     * @var array<string, string|Closure|null>
     */
    protected array $modelLabels = [];

    public function modelLabel(string $model, string | Closure | null $label): static
    {
        $this->modelLabels[$model] = $label;

        return $this;
    }

    public function modelLabels(array $modelLabels): static
    {
        $this->modelLabels = [
            ...$this->modelLabels,
            ...$modelLabels,
        ];

        return $this;
    }

    public function getModelLabel(Activity | ActivityModel $activity, Model | string $model): ?string
    {
        $modelClass = $model instanceof Model
            ? $model::class
            : $model;

        $value = $this->modelLabels[$modelClass] ?? null;

        return $this->evaluate(
            value: $value,
            namedInjections: [
                'activity' => $activity,
                'event' => $activity?->event,
                'model' => $model,
            ],
            typedInjections: [
                Activity::class => $activity,
                $activity::class => $activity,
            ],
        );
    }
}
