<?php

namespace RalphJSmit\Filament\Activitylog\Filament\Concerns\Timeline;

use BackedEnum;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Filament\Forms\Components\Field;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Entry;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use JsonSerializable;
use RalphJSmit\Filament\Activitylog;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Models\Activity as ActivityModel;

class ActivityTimelineItem
{
    public readonly ?Model $subject;

    public readonly ?string $subjectClass;

    public readonly ?string $model;

    public function __construct(
        public readonly Activity | ActivityModel $activity,
        public readonly Activitylog\Forms\Components\Timeline | Activitylog\Infolists\Components\Timeline $component,
        public readonly bool $isBatchActivityTimelineItem = false,
    ) {
        $this->subject = $this->activity->subject ?? (($this->activity->subject_type && $this->activity->subject_id) ? $this->activity->subject()->withTrashed()->first() : null);

        $this->subjectClass = $this->subject
            ? $this->subject::class
            : ($this->activity->subject_type ? (Relation::getMorphedModel($this->activity->subject_type) ?? $this->activity->subject_type) : null);

        if ($record = $this->component->getRecord()) {
            $this->model = $record::class;
        } else {
            $this->model = null;
        }
    }

    public function getDescription(): string | HtmlString
    {
        $recordTitle = e($this->getRecordTitle());
        $causerName = e($this->getCauserName());
        $changesSummary = $this->getChangesSummary();

        if ($description = $this->component->getEventDescription($this->activity, $causerName, $changesSummary)) {
            if (is_string($description)) {
                // Need to escape manually, otherwise vulnerable to HTML injection if it gets used straight-away in an `HtmlString`.
                $description = e($description);
            }

            if ($this->component->hasModifyEventDescriptionCallback()) {
                $description = $this->component->modifyEventDescription($this->activity, $description, $recordTitle, $causerName, $changesSummary);
            }

            if (is_string($description)) {
                // Escape manually again, say that e.g. a random attribute is prepended to the description and that is unescaped.
                $description = str(e($description))->inlineMarkdown()->toHtmlString();
            }

            return $description;
        }

        if (($description = $this->activity->description) && $this->activity->description !== $this->activity->event) {
            if ($this->component->hasModifyEventDescriptionCallback()) {
                $description = $this->component->modifyEventDescription($this->activity, $description, $recordTitle, $causerName, $changesSummary);
            }

            // Escape manually, say that e.g. a random attribute is prepended to the description and that is unescaped.
            return str(e($description))->inlineMarkdown()->toHtmlString();
        }

        if (! $this->subject) {
            // No need to escape, output from the `->generateEventDescription()` method is safe, because any problematic parts are escaped inside the method.
            $description = $this->getCustomEventDescription();

            if ($this->component->hasModifyEventDescriptionCallback()) {
                $description = $this->component->modifyEventDescription($this->activity, $description, $recordTitle, $causerName, $changesSummary);
            }

            if (is_string($description)) {
                // Escape manually, say that e.g. a random attribute is prepended to the description and that is unescaped.
                $description = str(e($description))->inlineMarkdown()->toHtmlString();
            }

            return $description;
        }

        $description = match ($this->activity->event) {
            'created' => $this->getCreatedDescription(),
            'updated' => $this->getUpdatedDescription(),
            'deleted' => $this->getDeletedDescription(),
            'restored' => $this->getRestoredDescription(),
            null => $this->getFallbackEventDescription(),
            default => $this->getCustomEventDescription(),
        };

        // No need to escape, output from the `->generateEventDescription()` method is safe, because any problematic parts are escaped inside the method.

        if ($this->component->hasModifyEventDescriptionCallback()) {
            $description = $this->component->modifyEventDescription($this->activity, $description, $recordTitle, $causerName, $changesSummary);
        }

        if (is_string($description)) {
            // Escape manually, say that e.g. a random attribute is prepended to the description and that is unescaped.
            $description = str(e($description))->inlineMarkdown()->toHtmlString();
        }

        return $description;
    }

    protected function generateEventDescription(string $event): string
    {
        $formattedEvent = str($event)->headline()->lower();

        $causerName = e($this->getCauserName());

        if ($causerUrl = $this->getCauserUrl()) {
            $causerName = "[{$causerName}]({$causerUrl})";
        }

        $replace = [
            'causerName' => $causerName,
            'changesSummary' => $this->subjectClass ? $this->getChangesSummary() : null,
            'modelLabel' => $this->subjectClass ? e($this->getSubjectModelLabel()) : null,
            'relationshipName' => $this->subjectClass && $this->model && $this->model !== $this->subjectClass ? e($this->getRelationshipName()) : null,
            'event' => $formattedEvent,
        ];

        if ($replace['relationshipName'] && $this->subject) {
            $replace['relatedRecordTitle'] = e($this->getRecordTitle());

            if (class_exists(\Filament\Facades\Filament::class) && ($panel = \Filament\Facades\Filament::getCurrentPanel())) {
                /** @var \Filament\Resources\Resource $modelResource */
                $modelResource = $panel->getModelResource($this->subjectClass ?? $this->model);

                if ($modelResource && ($modelResource::hasPage('view') || $modelResource::hasPage('edit'))) {
                    $relatedRecordUrl = $modelResource::hasPage('view') && $modelResource::canView($this->subject)
                        ? $modelResource::getUrl('view', ['record' => $this->subject])
                        : ($modelResource::hasPage('edit') && $modelResource::canEdit($this->subject) ? $modelResource::getUrl('edit', ['record' => $this->subject]) : null);

                    if ($relatedRecordUrl) {
                        $replace['relatedRecordTitle'] = "[{$replace['relatedRecordTitle']}]({$relatedRecordUrl})";
                    }
                }
            }
        }

        $replace['relatedRecordTitle'] ??= null;

        if (in_array($event, ['created', 'updated', 'deleted', 'restored'])) {
            $eventTranslationKey = $event;
        } elseif (! $this->subjectClass) {
            $eventTranslationKey = 'no-subject';
        } else {
            $eventTranslationKey = 'custom';
        }

        if ($event === 'updated') {
            $eventDescription = match (true) {
                // Causer + summary...
                $replace['causerName'] && $replace['changesSummary'] && ! $replace['relationshipName'] => __('filament-activitylog::translations.activity-timeline-item.event-descriptions.updated.causer-changes-summary', $replace),
                $replace['causerName'] && $replace['changesSummary'] && $replace['relationshipName'] && ! $replace['relatedRecordTitle'] => __('filament-activitylog::translations.activity-timeline-item.event-descriptions.updated.causer-changes-summary-relationship', $replace),
                $replace['causerName'] && $replace['changesSummary'] && $replace['relationshipName'] && $replace['relatedRecordTitle'] => __('filament-activitylog::translations.activity-timeline-item.event-descriptions.updated.causer-changes-summary-relationship-related-record-title', $replace),
                // Causer + no summary...
                $replace['causerName'] && ! $replace['changesSummary'] && ! $replace['relationshipName'] => __('filament-activitylog::translations.activity-timeline-item.event-descriptions.updated.causer-without-changes-summary', $replace),
                $replace['causerName'] && ! $replace['changesSummary'] && $replace['relationshipName'] && ! $replace['relatedRecordTitle'] => __('filament-activitylog::translations.activity-timeline-item.event-descriptions.updated.causer-without-changes-summary-relationship', $replace),
                $replace['causerName'] && ! $replace['changesSummary'] && $replace['relationshipName'] && $replace['relatedRecordTitle'] => __('filament-activitylog::translations.activity-timeline-item.event-descriptions.updated.causer-without-changes-summary-relationship-related-record-title', $replace),
                // No causer + summary...
                ! $replace['causerName'] && $replace['changesSummary'] && ! $replace['relationshipName'] => __('filament-activitylog::translations.activity-timeline-item.event-descriptions.updated.without-causer-changes-summary', $replace),
                ! $replace['causerName'] && $replace['changesSummary'] && $replace['relationshipName'] && ! $replace['relatedRecordTitle'] => __('filament-activitylog::translations.activity-timeline-item.event-descriptions.updated.without-causer-changes-summary-relationship', $replace),
                ! $replace['causerName'] && $replace['changesSummary'] && $replace['relationshipName'] && $replace['relatedRecordTitle'] => __('filament-activitylog::translations.activity-timeline-item.event-descriptions.updated.without-causer-changes-summary-relationship-related-record-title', $replace),
                // No causer + no summary
                ! $replace['causerName'] && ! $replace['changesSummary'] && ! $replace['relationshipName'] => __('filament-activitylog::translations.activity-timeline-item.event-descriptions.updated.without-causer-without-changes-summary', $replace),
                ! $replace['causerName'] && ! $replace['changesSummary'] && $replace['relationshipName'] && ! $replace['relatedRecordTitle'] => __('filament-activitylog::translations.activity-timeline-item.event-descriptions.updated.without-causer-without-changes-summary-relationship', $replace),
                ! $replace['causerName'] && ! $replace['changesSummary'] && $replace['relationshipName'] && $replace['relatedRecordTitle'] => __('filament-activitylog::translations.activity-timeline-item.event-descriptions.updated.without-causer-without-changes-summary-relationship-related-record-title', $replace),
            };
        } else {
            $eventDescription = match (true) {
                // Causer...
                $replace['causerName'] && ! $replace['relationshipName'] => __("filament-activitylog::translations.activity-timeline-item.event-descriptions.{$eventTranslationKey}.causer", $replace),
                $replace['causerName'] && $replace['relationshipName'] && ! $replace['relatedRecordTitle'] => __("filament-activitylog::translations.activity-timeline-item.event-descriptions.{$eventTranslationKey}.causer-relationship", $replace),
                $replace['causerName'] && $replace['relationshipName'] && $replace['relatedRecordTitle'] => __("filament-activitylog::translations.activity-timeline-item.event-descriptions.{$eventTranslationKey}.causer-relationship-related-record-title", $replace),
                // No causer...
                ! $replace['causerName'] && ! $replace['relationshipName'] => __("filament-activitylog::translations.activity-timeline-item.event-descriptions.{$eventTranslationKey}.without-causer", $replace),
                ! $replace['causerName'] && $replace['relationshipName'] && ! $replace['relatedRecordTitle'] => __("filament-activitylog::translations.activity-timeline-item.event-descriptions.{$eventTranslationKey}.without-causer-relationship", $replace),
                ! $replace['causerName'] && $replace['relationshipName'] && $replace['relatedRecordTitle'] => __("filament-activitylog::translations.activity-timeline-item.event-descriptions.{$eventTranslationKey}.without-causer-relationship-related-record-title", $replace),
            };
        }

        return $eventDescription;
    }

    protected function getCreatedDescription(): string
    {
        return $this->generateEventDescription('created');
    }

    protected function getUpdatedDescription(): string
    {
        return $this->generateEventDescription('updated');
    }

    protected function getDeletedDescription(): string
    {
        return $this->generateEventDescription('deleted');
    }

    protected function getRestoredDescription(): string
    {
        return $this->generateEventDescription('restored');
    }

    protected function getCustomEventDescription(): string
    {
        return $this->generateEventDescription($this->activity->event);
    }

    protected function getFallbackEventDescription(): string
    {
        return $this->activity->description;
    }

    public function getRecordTitle(): ?string
    {
        $subject = $this->subject;

        if (! $subject) {
            return null;
        }

        return $this->component->getRecordTitle($this->activity, $subject);
    }

    public function getCauserName(): ?string
    {
        $causer = $this->activity->causer;

        if ($this->component->hasCustomCauserNameCallback($causer)) {
            return trim($this->component->getCauserName($this->activity, $causer));
        }

        if (! $causer) {
            return null;
        }

        if ($causer instanceof \Filament\Models\Contracts\HasName) {
            return trim($causer->getFilamentName());
        }

        // Cannot directly access the attribute, since that could potentially trigger a `Model::preventAccessingMissingAttributes()` exception.
        if ($causer->getAttributes()['name'] ?? null) {
            return trim($causer->name);
        }

        if (($causer->getAttributes()['first_name'] ?? null) || ($causer->getAttributes()['last_name'] ?? null)) {
            return trim(collect([$causer->first_name, $causer->last_name])->filter()->implode(' '));
        }

        return null;
    }

    public function getCauserUrl(): ?string
    {
        return $this->component->getCauserUrl($this->activity, $this->activity->causer);
    }

    /**
     * All output returned must be escaped and is safe to be displayed.
     */
    public function getChangesSummary(): ?string
    {
        $attributes = collect($this->activity->changes()->get('attributes', []));
        $oldAttributes = collect($this->activity->changes()->get('old', []));

        $updatedAttributes = $attributes
            ->filter(function (mixed $value, string $key) use ($oldAttributes) {
                // For parsing the attribute value of old keys, we need to provide the `old` key as the `rawAttributePropertyKey`.
                // This will then be used to get the raw attributes of the _old_ Eloquent model using `$this->activity->properties`.
                return $this->parseAttributeValue($oldAttributes->get($key), $key, 'old')
                    !== $this->parseAttributeValue($value, $key);
            })
            ->reject(fn (mixed $value, string $key) => $this->subjectClass ? in_array($key, [
                (new $this->subjectClass())->getCreatedAtColumn(),
                (new $this->subjectClass())->getUpdatedAtColumn(),
                method_exists(new $this->subjectClass(), 'getDeletedAtColumn') ? (new $this->subjectClass())->getDeletedAtColumn() : null,
            ]) : false);

        return $updatedAttributes
            ->map(function (mixed $value, string $key) use ($oldAttributes) {
                $attributeLabel = $this->getAttributeLabel($key);

                $isChangesSummaryAttributeValuesVisible = $this->component->isChangesSummaryAttributeValuesVisible($key);

                if (! $isChangesSummaryAttributeValuesVisible) {
                    return $attributeLabel;
                }

                $isChangesSummaryOldAttributeValuesVisible = $this->component->isChangesSummaryAttributeOldValuesVisible($key);

                $attributeValue = $this->parseAttributeValue($value, $key);
                $oldAttributeValue = $isChangesSummaryOldAttributeValuesVisible
                    ? $this->parseAttributeValue($oldAttributes->get($key), $key, 'old')
                    : null;

                if (blank($attributeValue)) {
                    if (blank($oldAttributeValue)) {
                        return __('filament-activitylog::translations.activity-timeline-item.event-descriptions.changesSummary.attributeFromBlankToBlankWithOld', [
                            'attributeLabel' => $attributeLabel,
                            'oldAttributeValue' => $oldAttributeValue,
                            'newAttributeValue' => $attributeValue,
                        ]);
                    }

                    if ($isChangesSummaryOldAttributeValuesVisible) {
                        return __('filament-activitylog::translations.activity-timeline-item.event-descriptions.changesSummary.attributeToBlankWithOld', [
                            'attributeLabel' => $attributeLabel,
                            'oldAttributeValue' => $oldAttributeValue,
                            'newAttributeValue' => $attributeValue,
                        ]);
                    }

                    return __('filament-activitylog::translations.activity-timeline-item.event-descriptions.changesSummary.attributeToBlank', [
                        'attributeLabel' => $attributeLabel,
                        'newAttributeValue' => $attributeValue,
                    ]);
                }

                if ($isChangesSummaryOldAttributeValuesVisible) {
                    if (blank($oldAttributeValue)) {
                        return __('filament-activitylog::translations.activity-timeline-item.event-descriptions.changesSummary.attributeFromBlankWithOld', [
                            'attributeLabel' => $attributeLabel,
                            'oldAttributeValue' => $oldAttributeValue,
                            'newAttributeValue' => $attributeValue,
                        ]);
                    }

                    return __('filament-activitylog::translations.activity-timeline-item.event-descriptions.changesSummary.attributeWithOld', [
                        'attributeLabel' => $attributeLabel,
                        'oldAttributeValue' => $oldAttributeValue,
                        'newAttributeValue' => $attributeValue,
                    ]);
                }

                return __('filament-activitylog::translations.activity-timeline-item.event-descriptions.changesSummary.attribute', [
                    'attributeLabel' => $attributeLabel,
                    'newAttributeValue' => $attributeValue,
                ]);
            })
            ->join(', ', finalGlue: __('filament-activitylog::translations.activity-timeline-item.event-descriptions.changesSummary.finalGlue'));
    }

    public function getAttributeLabel(string $key): string
    {
        if ($attributeLabel = $this->component->getAttributeLabel($this->activity, $key)) {
            return $attributeLabel;
        }

        $attributeLabel = null;

        if ($this->component instanceof Activitylog\Forms\Components\Timeline) {
            $livewire = $this->component->getLivewire();

            if ($livewire instanceof HasForms) {
                $cachedContainers = $livewire->getCachedForms();
            }
        } else {
            $livewire = $this->component->getLivewire();

            if ($livewire instanceof HasInfolists) {
                $cachedContainers = $livewire->getCachedInfolists();
            }
        }

        foreach ($cachedContainers ?? [] as $cachedContainer) {
            if ($attributeLabel) {
                continue;
            }

            /** @var Form|Infolist $cachedContainer */
            $component = $cachedContainer->getComponent(function (\Filament\Forms\Components\Component | \Filament\Infolists\Components\Component $component) use ($key) {
                if (! $component instanceof Field && ! $component instanceof Entry) {
                    return false;
                }

                return $component->getName() === $key;
            }, withHidden: true);

            if ($component) {
                $attributeLabel = $component->getLabel();
            }
        }

        $attributeLabel ??= str($key)->headline();

        return str($attributeLabel)->lower();
    }

    public function parseAttributeValue(mixed $value, string $key, string $rawAttributePropertyKey = 'attributes'): ?string
    {
        $cast = $this->subjectClass ? ((new $this->subjectClass())->getCasts()[$key] ?? null) : null;

        if ($cast) {
            // If the $value is an array, then it could have been an array or collection originally, but it wasn't
            // saved using `useAttributeRawValues()`. This means that the `Json::decode()` cast will convert this
            // back to an array. If it's an array, we will then convert it back to a JSON string, and then let
            // the lower `getAttributeValue()` function handle the conversion to final array or collection.
            // Error: json_decode(): Argument #1 ($json) must be of type string, array given
            if (is_array($value)) {
                $value = Json::encode($value);
            }

            /** @var Model $model */
            $model = (new $this->subjectClass())->setRawAttributes([
                ...$this->activity->properties->get($rawAttributePropertyKey, []),
                // Ensure that the `$key` and `$value` we input are always specifically used.
                $key => $value,
            ]);

            $value = $model->getAttributeValue($key);
        }

        if ($this->component->getAttributeValueCallback($this->activity, $key)) {
            $attributeValue = $this->component->formatAttributeValue($this->activity, $key, $value);

            if (is_string($attributeValue)) {
                $attributeValue = e($attributeValue);
            }

            return $attributeValue;
        }

        if ($cast && $this->component->getAttributeCastCallback($this->activity, $cast)) {
            $attributeValue = $this->component->formatAttributeCast($this->activity, $cast, $value);

            if (is_string($attributeValue)) {
                $attributeValue = e($attributeValue);
            }

            return $attributeValue;
        }

        if ($value instanceof DateTimeInterface) {
            $castWithoutArguments = Str::contains($cast, ':') ? Str::before($cast, ':') : $cast;

            $format = match (true) {
                $castWithoutArguments === 'date' => 'Y-m-d',
                $castWithoutArguments === 'immutable_date' => 'Y-m-d',
                $castWithoutArguments === 'datetime' => 'Y-m-d H:i:s',
                $castWithoutArguments === 'immutable_datetime' => 'Y-m-d H:i:s',
                default => 'Y-m-d H:i:s',
            };

            return $value->translatedFormat($format);
        }

        if (is_array($value) || $value instanceof JsonSerializable) {
            if ($value instanceof JsonSerializable) {
                $value = $value->jsonSerialize();
            }

            if (is_array($value)) {
                return collect($value)
                    ->mapWithKeys(function (mixed $value, int | string $key) {
                        return [
                            is_string($key) ? e($key) : $key => is_string($value) ? e($value) : $value,
                        ];
                    })
                    ->toJson();
            }
        }

        if ($value instanceof HasLabel) {
            return e($value->getLabel());
        }

        if ($value instanceof BackedEnum) {
            return $value->name;
        }

        if (is_bool($value)) {
            return $value
                ? __('filament-activitylog::translations.activity-timeline-item.event-descriptions.changesSummary.values.boolean-1')
                : __('filament-activitylog::translations.activity-timeline-item.event-descriptions.changesSummary.values.boolean-0');
        }

        return e($value);
    }

    public function getSubjectModelLabel(): ?string
    {
        if (! $this->subjectClass && ! $this->model) {
            return null;
        }

        if ($modelLabel = $this->component->getModelLabel($this->activity, $this->subjectClass ?? $this->model)) {
            return $modelLabel;
        }

        $modelLabel = null;

        if (class_exists(\Filament\Facades\Filament::class) && ($panel = \Filament\Facades\Filament::getCurrentPanel())) {
            /** @var \Filament\Resources\Resource $modelResource */
            $modelResource = $panel->getModelResource($this->subjectClass ?? $this->model);

            if ($modelResource) {
                $modelLabel = $modelResource::getModelLabel();
            }
        }

        $modelLabel ??= \Filament\Support\get_model_label($this->subjectClass ?? $this->model);

        return str($modelLabel)->lower();
    }

    public function getRelationshipName(): ?string
    {
        return $this->getSubjectModelLabel();
    }

    public function getDateTime(): CarbonInterface
    {
        return $this->activity->created_at;
    }

    public function getDateTimeFormat(): string
    {
        return $this->component->getItemDateTimeFormat() ?? 'M jS, Y H:i:s';
    }

    public function getDateTimeTimezone(): ?string
    {
        return $this->component->getItemDateTimeTimezone();
    }

    public function getDateTimeFormatted(): string
    {
        $format = $this->getDateTimeFormat();

        $dateTime = $this->getDateTime();

        if ($timezone = $this->getDateTimeTimezone()) {
            $dateTime->setTimezone($timezone);
        }

        return $dateTime->translatedFormat($format);
    }

    public function getIcon(bool $isCompact = false): ?string
    {
        $icon = $this->component->getItemIcon($this->activity);

        if ($icon && str($icon)->startsWith('heroicon-m-') && ! $isCompact) {
            $icon = str($icon)->replace('heroicon-m-', 'heroicon-o-');
        }

        if ($icon && str($icon)->startsWith('heroicon-o-') && $isCompact) {
            $icon = str($icon)->replace('heroicon-o-', 'heroicon-m-');
        }

        if ($icon && str($icon)->startsWith('heroicon-s-') && $isCompact) {
            $icon = str($icon)->replace('heroicon-s-', 'heroicon-m-');
        }

        return $icon;
    }

    public function getIconColor(): null | string | array
    {
        return $this->component->getItemIconColor($this->activity);
    }

    public function getBadge(): ?string
    {
        return $this->component->getItemBadge($this->activity);
    }

    public function getBadgeColor(): null | string | array
    {
        return $this->component->getItemBadgeColor($this->activity);
    }

    public function getActions(): array
    {
        return $this->component->getItemActions($this->activity);
    }

    public function getBatchTimelineData(): ?Activitylog\Data\TimelineData
    {
        if ($this->isBatchActivityTimelineItem) {
            return null;
        }

        if (! $this->activity->batch_uuid) {
            return null;
        }

        if ($this->component->isBatchInline()) {
            return null;
        }

        $batchActivities = $this->component->getBatchActivities($this->activity, $this->activity->batch_uuid);

        if ($batchActivities->containsOneItem()) {
            return null;
        }

        return new Activitylog\Data\TimelineData(
            activityTimelineItems: $batchActivities->map(function (ActivityModel $activity) {
                return new ActivityTimelineItem($activity, $this->component, true);
            }),
            evaluationCallback: $this->component->evaluate(...),
            isCompact: true,
        );
    }

    /**
     * @deprecated Use `getDateTime()` instead.
     */
    public function getTime(): CarbonInterface
    {
        return $this->getDateTime();
    }
}
