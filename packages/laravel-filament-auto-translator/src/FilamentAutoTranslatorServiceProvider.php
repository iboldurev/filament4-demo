<?php

namespace RalphJSmit\Filament\AutoTranslator;

use Closure;
use Filament;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentAutoTranslatorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-filament-auto-translator');
    }

    public function boot(): void
    {
        parent::boot();

        $this->bootMacros();
    }

    protected function bootMacros(): void
    {
        $translationKeyMacro = function (string | Closure $key, bool | Closure $isAbsolute = false): Filament\Support\Components\ViewComponent | Filament\Support\Components\Component {
            /** @var Filament\Support\Components\ViewComponent|Filament\Support\Components\Component $this */
            $this->translationKey = $key;
            $this->isTranslationKeyAbsolute = $isAbsolute;

            return $this;
        };

        $getTranslationKeyMacro = function (): ?string {
            /** @var Filament\Support\Components\ViewComponent|Filament\Support\Components\Component $this */
            return $this->evaluate($this->translationKey ?? null);
        };

        $translationKeyAbsoluteMacro = function (bool | Closure $condition): static {
            /** @var Filament\Support\Components\ViewComponent|Filament\Support\Components\Component $this */
            $this->isTranslationKeyAbsolute = $condition;

            return $this;
        };

        $isTranslationKeyAbsoluteMacro = function (): bool {
            /** @var Filament\Support\Components\ViewComponent|Filament\Support\Components\Component $this */
            return $this->evaluate($this->isTranslationKeyAbsolute ?? false);
        };

        Filament\Support\Components\ViewComponent::macro('translationKey', $translationKeyMacro);
        Filament\Support\Components\ViewComponent::macro('getTranslationKey', $getTranslationKeyMacro);
        Filament\Support\Components\ViewComponent::macro('translationKeyAbsolute', $translationKeyAbsoluteMacro);
        Filament\Support\Components\ViewComponent::macro('isTranslationKeyAbsolute', $isTranslationKeyAbsoluteMacro);

        Filament\Support\Components\Component::macro('translationKey', $translationKeyMacro);
        Filament\Support\Components\Component::macro('getTranslationKey', $getTranslationKeyMacro);
        Filament\Support\Components\Component::macro('translationKeyAbsolute', $translationKeyAbsoluteMacro);
        Filament\Support\Components\Component::macro('isTranslationKeyAbsolute', $isTranslationKeyAbsoluteMacro);
    }
}
