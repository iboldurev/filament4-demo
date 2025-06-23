<?php

namespace RalphJSmit\Filament\Activitylog\Filament\Concerns\Timeline;

use Closure;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Models\Activity as ActivityModel;

trait HasEventDescriptions
{
    /**
     * @var array<string, array<string, string|Closure>>
     */
    protected array $eventDescriptions = [];

    protected ?Closure $modifyEventDescriptionUsing = null;

    protected bool | Closure $isChangesSummaryAttributeValuesVisible = true;

    protected bool | Closure $isChangesSummaryOldAttributeValuesVisible = true;

    public function eventDescription(string $event, string | Closure $description, string | array $subjectScopes = []): static
    {
        $subjectScopes = Arr::wrap($subjectScopes);

        if (! $subjectScopes) {
            $subjectScopes = ['default'];
        }

        foreach ($subjectScopes as $subjectScope) {
            $this->eventDescriptions[$subjectScope][$event] = $description;
        }

        return $this;
    }

    public function eventDescriptions(array $descriptions, string | array $subjectScopes = []): static
    {
        $subjectScopes = Arr::wrap($subjectScopes);

        if (! $subjectScopes) {
            $subjectScopes = ['default'];
        }

        foreach ($subjectScopes as $subjectScope) {
            $this->eventDescriptions[$subjectScope] = [
                ...$this->eventDescriptions[$subjectScope] ?? [],
                ...$descriptions,
            ];
        }

        return $this;
    }

    public function getEventDescription(Activity | ActivityModel $activity, ?string $causerName = null, ?string $changesSummary = null): null | string | HtmlString
    {
        $subjectType = $activity->subject_type ? (Relation::getMorphedModel($activity->subject_type) ?? $activity->subject_type) : null;

        $value = $subjectType && Arr::has($this->eventDescriptions, "{$subjectType}.{$activity->event}")
            ? $this->eventDescriptions[$subjectType][$activity->event]
            : (
                $subjectType && Arr::has($this->eventDescriptions, "{$subjectType}.*")
                    ? $this->eventDescriptions[$subjectType]['*']
                    : $this->eventDescriptions['default'][$activity->event] ?? $this->eventDescriptions['default']['*'] ?? null
            );

        return $this->evaluate(
            value: $value,
            namedInjections: [
                'activity' => $activity,
                'subject' => fn () => $activity->subject,
                'causer' => fn () => $activity->causer,
                'event' => $activity->event,
                'causerName' => $causerName,
                'changesSummary' => $changesSummary,
            ],
            typedInjections: [
                Activity::class => $activity,
                $activity::class => $activity,
            ],
        );
    }

    public function modifyEventDescriptionUsing(Closure $callback): static
    {
        $this->modifyEventDescriptionUsing = $callback;

        return $this;
    }

    public function hasModifyEventDescriptionCallback(): bool
    {
        return $this->modifyEventDescriptionUsing !== null;
    }

    public function modifyEventDescription(Activity | ActivityModel $activity, string $eventDescription, ?string $recordTitle, ?string $causerName, ?string $changesSummary = null): string | HtmlString
    {
        return $this->evaluate(
            value: $this->modifyEventDescriptionUsing,
            namedInjections: [
                'eventDescription' => $eventDescription,
                'activity' => $activity,
                'subject' => fn () => $activity->subject,
                'causer' => fn () => $activity->causer,
                'event' => $activity->event,
                'recordTitle' => $recordTitle,
                'causerName' => $causerName,
                'changesSummary' => $changesSummary,
            ],
            typedInjections: [
                Activity::class => $activity,
                $activity::class => $activity,
            ],
        );
    }

    public function changesSummaryAttributeValues(bool | Closure $condition = true): static
    {
        $this->isChangesSummaryAttributeValuesVisible = $condition;

        return $this;
    }

    public function changesSummaryOldAttributeValues(bool | Closure $condition = true): static
    {
        $this->isChangesSummaryOldAttributeValuesVisible = $condition;

        return $this;
    }

    public function isChangesSummaryAttributeValuesVisible(string $attribute): bool
    {
        return $this->evaluate($this->isChangesSummaryAttributeValuesVisible, [
            'attribute' => $attribute,
        ]);
    }

    public function isChangesSummaryAttributeOldValuesVisible(string $attribute): bool
    {
        return $this->evaluate($this->isChangesSummaryOldAttributeValuesVisible, [
            'attribute' => $attribute,
        ]);
    }
}
