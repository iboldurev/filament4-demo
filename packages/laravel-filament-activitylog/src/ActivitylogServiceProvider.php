<?php

namespace RalphJSmit\Filament\Activitylog;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ActivitylogServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-filament-activitylog')
            ->hasViews()
            ->hasTranslations();
    }
}
