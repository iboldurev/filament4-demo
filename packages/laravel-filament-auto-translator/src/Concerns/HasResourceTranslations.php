<?php

namespace RalphJSmit\Filament\AutoTranslator\Concerns;

use Countable;
use RalphJSmit\Filament\AutoTranslator\Enums\PageTranslationContext;
use RalphJSmit\Filament\AutoTranslator\FilamentAutoTranslator;

trait HasResourceTranslations
{
    public static function getTranslation(string $key, array $replace = [], Countable | float | int | null $number = null, bool $allowNull = false, ?PageTranslationContext $pageTranslationContext = null, ?string $pageTranslationContextKey = null): mixed
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

        $translationKey = str($namespace)
            ->when(filled($pageTranslationContextKey))->append(".{$pageTranslationContextKey}")
            ->when(blank($pageTranslationContextKey) && $pageTranslationContext === PageTranslationContext::Form)->append('.form')
            ->when(blank($pageTranslationContextKey) && $pageTranslationContext === PageTranslationContext::Infolist)->append('.infolist')
            ->when(blank($pageTranslationContextKey) && $pageTranslationContext === PageTranslationContext::Table)->append('.table')
            ->append(".{$key}")
            ->toString();

        if (! app('translator')->has($translationKey) && ($allowNull || app()->isProduction())) {
            return null;
        }

        if ($number !== null) {
            return trans_choice($translationKey, $number, $replace);
        }

        return __($translationKey, $replace);
    }

    public static function getModelLabel(): string
    {
        return static::getTranslation('model_label', allowNull: true) ?? parent::getModelLabel();
    }

    public static function getPluralModelLabel(): string
    {
        return static::getTranslation('plural_model_label', allowNull: true) ?? parent::getPluralModelLabel();
    }

    public static function getNavigationLabel(): string
    {
        return static::getTranslation('navigation_label', allowNull: true) ?? parent::getNavigationLabel();
    }

    public static function getNavigationGroup(): ?string
    {
        return static::getTranslation('navigation_group', allowNull: true) ?? parent::getNavigationGroup();
    }

    public static function getBreadcrumb(): string
    {
        return static::getTranslation('breadcrumb', allowNull: true) ?? parent::getBreadcrumb();
    }
}
