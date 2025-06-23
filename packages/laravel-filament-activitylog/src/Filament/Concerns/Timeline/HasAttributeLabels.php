<?php

namespace RalphJSmit\Filament\Activitylog\Filament\Concerns\Timeline;

use Closure;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Models\Activity as ActivityModel;

trait HasAttributeLabels
{
    /**
     * @var array<string, string|Closure>
     */
    protected array $attributeLabels = [];

    public function attributeLabel(string $key, string | Closure $label, string | array $subjectScopes = []): static
    {
        $subjectScopes = Arr::wrap($subjectScopes);

        if (! $subjectScopes) {
            $subjectScopes = ['default'];
        }

        foreach ($subjectScopes as $subjectScope) {
            $this->attributeLabels[$subjectScope][$key] = $label;
        }

        return $this;
    }

    public function attributeLabels(array $attributeLabels, string | array $subjectScopes = []): static
    {
        $subjectScopes = Arr::wrap($subjectScopes);

        if (! $subjectScopes) {
            $subjectScopes = ['default'];
        }

        foreach ($subjectScopes as $subjectScope) {
            $this->attributeLabels[$subjectScope] = [
                ...$this->attributeLabels[$subjectScope] ?? [],
                ...$attributeLabels,
            ];
        }

        return $this;
    }

    public function getAttributeLabel(Activity | ActivityModel $activity, string $key): ?string
    {
        $subjectType = $activity->subject_type ? (Relation::getMorphedModel($activity->subject_type) ?? $activity->subject_type) : null;

        $value = $subjectType && Arr::has($this->attributeLabels, "{$subjectType}.{$key}")
            ? $this->attributeLabels[$subjectType][$key]
            : $this->attributeLabels['default'][$key] ?? null;

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
