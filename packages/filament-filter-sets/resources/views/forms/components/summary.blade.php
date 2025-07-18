<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    @php
        $id = $getId();
        $isDisabled = $isDisabled();
        $placeholder = $getPlaceholder();
        $splitKeys = $getSplitKeys();
        $statePath = $getStatePath();
    @endphp

    <div
        ax-load
        ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('tags-input', 'filament/forms') }}"
        x-data="tagsInputFormComponent({
                    state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
                    splitKeys: @js($splitKeys),
                })"
        x-ignore
        {{
            $attributes
                ->merge($getExtraAttributes(), escape: false)
                ->merge($getExtraAlpineAttributes(), escape: false)
                ->class(['fi-fo-tags-input'])
        }}
    >
        <div
            @class([
                'block w-full rounded-lg shadow-sm ring-1 transition duration-75',
                'bg-gray-50 dark:bg-transparent' => $isDisabled,
                'bg-white focus-within:ring-2 dark:bg-white/5' => ! $isDisabled,
                'ring-danger-600 focus-within:ring-danger-600 dark:ring-danger-500 dark:focus-within:ring-danger-500' => $errors->has($statePath),
                'ring-gray-950/10 focus-within:ring-primary-600 dark:focus-within:ring-primary-500' => ! $errors->has($statePath),
                'dark:ring-white/20' => (! $isDisabled) && (! $errors->has($statePath)),
                'dark:ring-white/10' => $isDisabled && (! $errors->has($statePath)),
            ])
        >
            <datalist id="{{ $id }}-suggestions">
                @foreach ($getSuggestions() as $suggestion)
                    <template
                        x-bind:key="@js($suggestion)"
                        x-if="! state.includes(@js($suggestion))"
                    >
                        <option value="{{ $suggestion }}" />
                    </template>
                @endforeach
            </datalist>

            <div wire:ignore>
                <template x-cloak x-if="state?.length">
                    <div
                        @class([
                            'flex w-full flex-wrap gap-1.5 p-2',
                        ])
                    >
                        <template
                            x-for="tag in state"
                            x-bind:key="tag"
                            class="hidden"
                        >
                            <x-filament::badge>
                                {{ $getTagPrefix() }}

                                <span class="text-start" x-text="tag"></span>

                                {{ $getTagSuffix() }}

                                @if (! $isDisabled)
                                    <x-slot
                                        name="deleteButton"
                                        x-on:click="deleteTag(tag)"
                                    ></x-slot>
                                @endif
                            </x-filament::badge>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-dynamic-component>
