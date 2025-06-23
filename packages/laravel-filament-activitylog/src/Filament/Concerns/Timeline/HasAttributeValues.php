<?php

namespace RalphJSmit\Filament\Activitylog\Filament\Concerns\Timeline;

use Closure;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Models\Activity as ActivityModel;

trait HasAttributeValues
{
    /**
     * @var array<string, array<string, Closure>>
     */
    protected array $attributeValues = [];

    public function attributeValue(string $key, Closure $formatUsing, string | array $subjectScopes = []): static
    {
        $subjectScopes = Arr::wrap($subjectScopes);

        if (! $subjectScopes) {
            $subjectScopes = ['default'];
        }

        foreach ($subjectScopes as $subjectScope) {
            $this->attributeValues[$subjectScope][$key] = $formatUsing;
        }

        return $this;
    }

    public function attributeValues(array $attributeValues, string | array $subjectScopes = []): static
    {
        $subjectScopes = Arr::wrap($subjectScopes);

        if (! $subjectScopes) {
            $subjectScopes = ['default'];
        }

        foreach ($subjectScopes as $subjectScope) {
            $this->attributeValues[$subjectScope] = [
                ...$this->attributeValues[$subjectScope] ?? [],
                ...$attributeValues,
            ];
        }

        return $this;
    }

    public function getAttributeValueCallback(Activity | ActivityModel $activity, string $key): ?Closure
    {
        $subjectType = $activity->subject_type ? (Relation::getMorphedModel($activity->subject_type) ?? $activity->subject_type) : null;

        return $subjectType && Arr::has($this->attributeValues, "{$subjectType}.{$key}")
            ? $this->attributeValues[$subjectType][$key]
            : $this->attributeValues['default'][$key] ?? null;
    }

    public function formatAttributeValue(Activity | ActivityModel $activity, string $key, mixed $value): ?string
    {
        return $this->evaluate(
            value: $this->getAttributeValueCallback($activity, $key),
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
