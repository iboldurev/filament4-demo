<x-dynamic-component
    :component="$getEntryWrapperView()"
    :entry="$entry"
>
    <x-filament-activitylog::timeline
        :timeline-data="$getTimelineData()"
    />
</x-dynamic-component>