<?php

namespace RalphJSmit\Filament\AutoTranslator\Concerns;

use Countable;
use RalphJSmit\Filament\AutoTranslator\Enums\PageTranslationContext;
use RalphJSmit\Filament\AutoTranslator\FilamentAutoTranslator;

trait HasClusterTranslations
{
    public static function getTranslation(string $key, array $replace = [], Countable|float|int|null $number = null, bool $allowNull = false, ?PageTranslationContext $pageTranslationContext = null, ?string $pageTranslationContextKey = null): mixed
    {
        $namespace = str(static::class);

        $translationGroups = FilamentAutoTranslator::get()->getTranslationGroups();

        if ($namespace->contains(array_keys($translationGroups))) {
            $namespace = $namespace->replace(
                array_keys($translationGroups),
                array_values($translationGroups),
            );
        } else {
            $namespace = $namespace
                ->after('Filament')
                ->prepend('Filament')
                ->trim('\\');
        }

        $namespace = $namespace
            ->kebab()
            ->replace('\\-', '\\')
            ->replace('\\', '/')
            ->rtrim('/');

        $translationKey = "{$namespace}.{$key}";

        if (! app('translator')->has($translationKey) && ($allowNull || app()->isProduction())) {
            return null;
        }

        if ($number !== null) {
            return trans_choice($translationKey, $number, $replace);
        }

        return __($translationKey, $replace);
    }

    public static function getNavigationLabel(): string
    {
        return static::getTranslation('navigation_label') ?? parent::getNavigationLabel();
    }

    public static function getNavigationGroup(): ?string
    {
        return static::getTranslation('navigation_group') ?? parent::getNavigationGroup();
    }

    public static function getClusterBreadcrumb(): ?string
    {
        return static::getTranslation('cluster_breadcrumb') ?? parent::getClusterBreadcrumb();
    }
}
