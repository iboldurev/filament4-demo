<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <x-filament-activitylog::timeline
        :timeline-data="$getTimelineData()"
    />
</x-dynamic-component>