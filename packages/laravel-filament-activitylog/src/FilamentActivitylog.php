<?php

namespace RalphJSmit\Filament\Activitylog;

use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentActivitylog implements Plugin
{
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
        return 'ralphjsmit/laravel-filament-activitylog';
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        //
    }

    protected function setUp(): void
    {
        //
    }
}
