<?php

namespace RalphJSmit\Filament\Activitylog\Filament\Concerns;

use Closure;
use Filament\Actions\StaticAction;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Model;
use RalphJSmit\Filament\Activitylog\Infolists\Components\Timeline;

trait ConfigureTimelineAction
{
    protected ?Closure $modifyTimelineCallback = null;

    protected function configureTimelineAction(): void
    {
        $this
            ->name('activities')
            ->icon('heroicon-o-bars-arrow-down')
            ->slideOver()
            ->modalWidth(MaxWidth::ExtraLarge)
            ->modalSubmitAction(fn (StaticAction $action) => $action->hidden())
            ->label(__('filament-activitylog::translations.actions.timeline-action.label'))
            ->modalCancelActionLabel(__('filament-activitylog::translations.actions.timeline-action.modal_cancel_action_label'))
            ->mountUsing(function (Model $record, Infolist $infolist) {
                $infolist->record($record);
            })
            ->infolist(fn (Infolist $infolist) => [
                Timeline::make()
                    ->hiddenLabel()
                    ->when($this->hasModifyTimelineCallback(), function (Timeline $timeline) use ($infolist) {
                        return $infolist->evaluate(
                            $this->getModifyTimelineCallback(),
                            namedInjections: [
                                'timeline' => $timeline,
                            ],
                            typedInjections: [
                                $timeline::class => $timeline,
                            ]
                        );
                    }),
            ]);
    }

    public function modifyTimelineUsing(Closure $callback): static
    {
        $this->modifyTimelineCallback = $callback;

        return $this;
    }

    public function hasModifyTimelineCallback(): bool
    {
        return $this->modifyTimelineCallback !== null;
    }

    public function getModifyTimelineCallback(): ?Closure
    {
        return $this->modifyTimelineCallback;
    }
}
