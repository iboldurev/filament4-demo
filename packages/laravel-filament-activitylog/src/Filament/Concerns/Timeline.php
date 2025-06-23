<?php

namespace RalphJSmit\Filament\Activitylog\Filament\Concerns;

use RalphJSmit\Filament\Activitylog\Data\TimelineData;
use RalphJSmit\Filament\Activitylog\Filament\Concerns\Timeline\ActivityTimelineItem;
use Spatie\Activitylog\Contracts\Activity;

trait Timeline
{
    use Timeline\CanBeCollapsed;
    use Timeline\CanBeCompacted;
    use Timeline\CanBeSearchable;
    use Timeline\HasActivities;
    use Timeline\HasActivityBatches;
    use Timeline\HasAttributeCasts;
    use Timeline\HasAttributeLabels;
    use Timeline\HasAttributeValues;
    use Timeline\HasCauser;
    use Timeline\HasEmptyState;
    use Timeline\HasEventDescriptions;
    use Timeline\HasItemActions;
    use Timeline\HasItemBadge;
    use Timeline\HasItemBadgeColor;
    use Timeline\HasItemDateTimeFormat;
    use Timeline\HasItemDateTimeTimezone;
    use Timeline\HasItemIcon;
    use Timeline\HasItemIconColor;
    use Timeline\HasMaxHeight;
    use Timeline\HasModelLabel;

    public function getTimelineData(): TimelineData
    {
        return new TimelineData(
            activityTimelineItems: $this->getActivities()->load(['subject', 'causer'])->map(function (Activity $activity): ActivityTimelineItem {
                return new ActivityTimelineItem($activity, $this);
            }),
            evaluationCallback: $this->evaluate(...),
            emptyStateHeading: $this->emptyStateHeading,
            emptyStateDescription: $this->emptyStateDescription,
            emptyStateIcon: $this->emptyStateIcon,
            isCompact: $this->isCompact,
            isSearchable: $this->isSearchable,
            maxHeight: $this->maxHeight,
            modelLabel: (function () {
                if ($this instanceof \RalphJSmit\Filament\Activitylog\Infolists\Components\Timeline) {
                    $record = $this->getRecord();

                    $model = $record ? $record::class : null;
                } else {
                    $model = $this->getModel();
                }

                $modelLabel = $this->modelLabels[$model] ?? null;

                if ($modelLabel) {
                    return $modelLabel;
                }

                return $model ? \Filament\Support\get_model_label($model) : null;
            })()
        );
    }
}
