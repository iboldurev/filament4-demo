<?php

namespace RalphJSmit\Filament\Activitylog\Filament\Concerns\Timeline;

use Closure;
use Filament\Models\Contracts\HasName;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Models\Activity as ActivityModel;

trait HasActivities
{
    protected ?Closure $getActivitiesCallback = null;

    protected ?Closure $modifyActivitiesCallback = null;

    protected ?Closure $modifyActivitiesQueryCallback = null;

    /**
     * @var array<string|array-key, string|null|array<string|array-key, string|Closure|null>|Closure>
     */
    protected array $withRelations = [];

    /**
     * @var array<string, string|Closure>
     */
    protected array $recordTitleAttributes = [];

    /**
     * @var array<string, Closure>
     */
    protected array $recordTitleCallbacks = [];

    public function withRelation(string $relation, ?Closure $modifyQueryUsing = null): static
    {
        $this->withRelations[] = [$relation => $modifyQueryUsing];

        return $this;
    }

    public function withRelations(array | Closure $relations): static
    {
        $this->withRelations[] = $relations;

        return $this;
    }

    public function getWithRelations(): Collection
    {
        $withRelations = collect();

        foreach ($this->withRelations as $key => $value) {
            if (is_string($key)) {
                // If $key is string, then $key represents a relationship name. $value could be either null or a closure with a modifyQueryUsing.
                $withRelations[$key] = $value;
            } else {
                // If $key is integer, then $value represents just the only thing we're interested in. It could be either a string or an array of strings/strings => closure|null.
                $value = $this->evaluate($value);

                // If $value is string, then $value represents a relationship name.
                if (is_string($value)) {
                    $withRelations[$value] = null;
                } else {
                    foreach ($value as $valueKey => $valueValue) {
                        // If $valueKey is string, then $valueKey represents a relationship name. $valueValue could be either null or a closure with a modifyQueryUsing.
                        if (is_string($valueKey)) {
                            $withRelations[$valueKey] = $valueValue;
                        } else {
                            $withRelations[$valueValue] = null;
                        }
                    }
                }
            }
        }

        return $withRelations;
    }

    public function recordTitleAttribute(string $model, string | Closure $titleAttributes): static
    {
        $this->recordTitleAttributes[$model] = $titleAttributes;

        return $this;
    }

    /**
     * @param  array<string, string|Closure>  $titleAttributes
     */
    public function recordTitleAttributes(array $titleAttributes): static
    {
        $this->recordTitleAttributes = [
            ...$this->recordTitleAttributes,
            ...$titleAttributes,
        ];

        return $this;
    }

    public function getRecordTitleAttribute(Model $model): null | string | Closure
    {
        return $this->recordTitleAttributes[$model::class] ?? null;
    }

    public function getRecordTitleUsing(string $model, ?Closure $callback): static
    {
        $this->recordTitleCallbacks[$model] = $callback;

        return $this;
    }

    /**
     * @param  array<string, ?Closure>  $callbacks
     */
    public function getRecordTitlesUsing(array $callbacks): static
    {
        $this->recordTitleCallbacks = [
            ...$this->recordTitleCallbacks,
            ...$callbacks,
        ];

        return $this;
    }

    public function hasCustomRecordTitleCallback(Model $model): bool
    {
        return isset($this->recordTitleCallbacks[$model::class]);
    }

    public function getRecordTitle(Activity | ActivityModel $activity, Model $model): ?string
    {
        if ($this->hasCustomRecordTitleCallback($model)) {
            return $this->evaluate(
                value: $this->recordTitleCallbacks[$model::class],
                namedInjections: [
                    'activity' => $activity,
                    'model' => $model,
                ],
                typedInjections: [
                    $model::class => $model,
                    Activity::class => $activity,
                    $activity::class => $activity,
                ],
            );
        }

        $recordTitleAttribute = $this->getRecordTitleAttribute($model);

        if ($recordTitleAttribute) {
            return $model->getAttributeValue($recordTitleAttribute);
        }

        if ($model instanceof HasName) {
            return $model->getFilamentName();
        }

        if ($model instanceof HasLabel) {
            return $model->getLabel();
        }

        // Cannot directly access the attribute, since that could potentially trigger a `Model::preventAccessingMissingAttributes()` exception.
        if ($model->getAttributes()['name'] ?? null) {
            return $model->name;
        }

        if ($model->getAttributes()['title'] ?? null) {
            return $model->title;
        }

        if ($model->getAttributes()['label'] ?? null) {
            return $model->label;
        }

        return null;
    }

    public function getActivitiesUsing(?Closure $callback): static
    {
        $this->getActivitiesCallback = $callback;

        return $this;
    }

    public function hasCustomGetActivitiesCallback(): bool
    {
        return isset($this->getActivitiesCallback);
    }

    public function modifyActivitiesUsing(?Closure $callback): static
    {
        $this->modifyActivitiesCallback = $callback;

        return $this;
    }

    public function modifyActivitiesQueryUsing(?Closure $callback): static
    {
        $this->modifyActivitiesQueryCallback = $callback;

        return $this;
    }

    /**
     * @return EloquentCollection<array-key, Activity>
     */
    public function getActivities(): EloquentCollection
    {
        $callback = $this->getActivitiesCallback ?? function (Model $record) {
            /** @var Builder $query */
            $query = \Spatie\Activitylog\ActivitylogServiceProvider::determineActivityModel()::query();

            if ($this->modifyActivitiesQueryCallback) {
                $query = $this->evaluate(
                    value: $this->modifyActivitiesQueryCallback,
                    namedInjections: [
                        'query' => $query,
                    ],
                    typedInjections: [
                        $query::class => $query,
                    ]
                );
            }

            $withRelations = $this
                ->getWithRelations()
                ->map(function (?Closure $modifyQueryUsing, string $relation) use ($record): Relation {
                    /** @var Relation $relation */
                    $relation = $record->{$relation}();

                    if (! $modifyQueryUsing) {
                        return $relation;
                    }

                    return $this->evaluate(
                        $modifyQueryUsing,
                        namedInjections: [
                            'relation' => $relation,
                            'query' => $relation,
                        ],
                        typedInjections: [
                            $relation::class => $relation,
                            \Illuminate\Contracts\Database\Eloquent\Builder::class => $relation,
                        ],
                    ) ?? $relation;
                })
                ->mapWithKeys(function (Relation $relation) {
                    return [$relation->getRelated()::class => $relation->pluck($relation->getRelated()->getQualifiedKeyName())];
                });

            // First condition, where()
            $query->whereMorphedTo('subject', $record);

            // Next all ->orWhere()'s
            foreach ($withRelations as $modelClass => $modelKeys) {
                $query->orWhere(function (Builder $query) use ($modelClass, $modelKeys) {
                    if ($this->modifyActivitiesQueryCallback) {
                        $query = $this->evaluate(
                            value: $this->modifyActivitiesQueryCallback,
                            namedInjections: [
                                'query' => $query,
                            ],
                            typedInjections: [
                                $query::class => $query,
                            ]
                        );
                    }

                    $query
                        ->where('subject_type', (new $modelClass())->getMorphClass())
                        ->whereIn('subject_id', $modelKeys->all());
                });
            }

            // If there are more than one activity in the query for the same batch, then the entire batch would be displayed twice.
            // Therefore, we will return 1) all items not belonging to a batch and 2) for each batch, only the the highest ID.
            // This way we will display max. 1 batch sequence, and in chronological ordering by taking the highest ID.
            $lastActivitiesFromBatch = $query
                ->clone()
                ->whereNotNull('batch_uuid')
                ->groupBy('batch_uuid')
                ->select(new Expression('MAX(id) as id'))
                ->toBase()
                ->pluck('id');

            $query->where(function (Builder $query) use ($lastActivitiesFromBatch) {
                return $query->whereNull('batch_uuid')->orWhereIn('id', $lastActivitiesFromBatch);
            });

            return $query
                ->latest()
                ->orderByDesc('id')
                ->get()
                ->unique()
                ->unique(fn (ActivityModel | Activity $activity) => $activity->batch_uuid ?: $activity->id);
        };

        /** @var EloquentCollection<array-key, Activity> $activities */
        $activities = $this->evaluate($callback);

        if ($this->isBatchInline()) {
            $activities = $activities->merge($this->getInlineBatchActivities($activities));
        }

        if ($this->hasCustomGetActivitiesCallback()) {
            $activities = $activities->filter(function (Activity $activity) use ($activities) {
                if (blank($activity->batch_uuid)) {
                    return true;
                }

                $activitiesForBatch = $activities->where('batch_uuid', $activity->batch_uuid);

                if ($activitiesForBatch->count() < 2) {
                    return true;
                }

                $activityFromBatchWithHighestId = $activitiesForBatch->max('id');

                return $activity->getKey() === $activityFromBatchWithHighestId;
            });
        }

        if ($this->modifyActivitiesCallback) {
            $activities = $this->evaluate($this->modifyActivitiesCallback, ['activities' => $activities]);
        }

        return $activities->sortByDesc('created_at')->unique()->values();
    }

    /**
     * @return EloquentCollection<array-key, Activity>
     */
    protected function getInlineBatchActivities(EloquentCollection $activities): EloquentCollection
    {
        $callback = $this->getBatchActivitiesCallback ?? function (EloquentCollection $activities) {
            $query = ActivityModel::query()
                ->whereIn('batch_uuid', $activities->pluck('batch_uuid')->filter()->unique())
                ->whereNotIn('id', $activities->pluck('id'))
                ->whereHas('subject');

            if ($this->modifyBatchActivitiesQueryCallback) {
                $query = $this->evaluate(
                    value: $this->modifyBatchActivitiesQueryCallback,
                    namedInjections: [
                        'query' => $query,
                        'activities' => $activities,
                    ],
                    typedInjections: [
                        $query::class => $query,
                    ]
                );
            }

            return $query->get();
        };

        /** @var EloquentCollection<array-key, Activity> */
        return $this->evaluate(
            value: $callback,
            namedInjections: [
                'activities' => $activities,
            ],
        );
    }
}
