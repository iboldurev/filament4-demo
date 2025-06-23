<?php

namespace RalphJSmit\Filament\AutoTranslator\FilamentAutoTranslator;

trait HasTranslationGroups
{
    /**
     * @var array<string, string>
     */
    protected array $translationGroups = [];

    public function translationGroups(array $translationGroups, bool $merge = true): static
    {
        if ($merge) {
            $this->translationGroups = [...$this->translationGroups, ...$translationGroups];
        } else {
            $this->translationGroups = $translationGroups;
        }

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getTranslationGroups(): array
    {
        return $this->translationGroups;
    }
}
