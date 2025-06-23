<?php

namespace RalphJSmit\Filament\AutoTranslator;

use Filament\Contracts\Plugin;
use Filament\Panel;
use RalphJSmit\Filament\AutoTranslator\FilamentAutoTranslator as Concerns;

class FilamentAutoTranslator implements Plugin
{
    use Concerns\HasTranslationGroups;

    public static function make(): static
    {
        $plugin = app(static::class);

        $plugin->setUp();

        return $plugin;
    }

    public static function get(): static
    {
        return filament(app(static::class)->getId());
    }

    public static function isRegistered(): bool
    {
        return filament()->hasPlugin(app(static::class)->getId());
    }

    public function getId(): string
    {
        return 'ralphjsmit/laravel-filament-auto-translator';
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        app(AutoTranslator::class)->boot();
    }

    protected function setUp(): void
    {
        //
    }
}
