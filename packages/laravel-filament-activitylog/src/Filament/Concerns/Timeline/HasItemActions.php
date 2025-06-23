<?php

namespace RalphJSmit\Filament\Activitylog\Filament\Concerns\Timeline;

use Filament\Forms;
use Filament\Infolists;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Models\Activity as ActivityModel;

trait HasItemActions
{
    /**
     * @var array<string, array<string, <array<array-key, Forms\Components\Actions\Action|Infolists\Components\Actions\Action>>>>
     */
    protected array $itemActions = [];

    /**
     * @param  array<array-key, Forms\Components\Actions\Action|Infolists\Components\Actions\Action>  $actions
     */
    public function itemActions(string $event, array $actions, string | array $subjectScopes = []): static
    {
        $subjectScopes = Arr::wrap($subjectScopes);

        if (! $subjectScopes) {
            $subjectScopes = ['default'];
        }

        foreach ($actions as $action) {
            $action->component($this);
        }

        $this->registerActions($actions);

        foreach ($subjectScopes as $subjectScope) {
            $this->itemActions[$subjectScope][$event] = [
                ...$this->itemActions[$subjectScope][$event] ?? [],
                ...$actions,
            ];
        }

        return $this;
    }

    /**
     * @return array<array-key, Forms\Components\Actions\Action|Infolists\Components\Actions\Action>
     */
    public function getItemActions(Activity | ActivityModel $activity): array
    {
        $subjectType = $activity->subject_type ? (Relation::getMorphedModel($activity->subject_type) ?? $activity->subject_type) : null;

        /** @var array<array-key, Forms\Components\Actions\Action|Infolists\Components\Actions\Action> $actions */
        $actions = $subjectType && Arr::has($this->itemActions, "{$subjectType}.{$activity->event}")
            ? $this->itemActions[$subjectType][$activity->event]
            : (
                $subjectType && Arr::has($this->itemActions, "{$subjectType}.*")
                        ? $this->itemActions[$subjectType]['*']
                        : $this->itemActions['default'][$activity->event] ?? $this->itemActions['default']['*'] ?? []
            );

        return array_map(
            callback: function (Forms\Components\Actions\Action | Infolists\Components\Actions\Action $action) use ($activity) {
                $clone = clone $action;

                $clone->arguments(['activity_id' => $activity->getKey()]);

                return $clone;
            },
            array: $actions
        );
    }
}
