<?php

namespace RalphJSmit\Filament\AutoTranslator\Concerns;

use Countable;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Model;
use RalphJSmit\Filament\AutoTranslator\Enums\PageTranslationContext;

/**
 * @mixin RelationManager
 */
trait HasResourceRelationManagerTranslations
{
    public static function getTranslation(string $key, array $replace = [], Countable | float | int | null $number = null, bool $allowNull = false, ?PageTranslationContext $pageTranslationContext = null, ?string $pageTranslationContextKey = null): mixed
    {
        $resourceClass = str(static::class)
            ->before('RelationManagers\\')
            ->trim('\\')
            ->toString();

        $relationManagerKey = str(static::class)
            ->classBasename()
            ->snake();

        $translationKey = str("relation_managers.{$relationManagerKey}")
            ->when(filled($pageTranslationContextKey))->append(".{$pageTranslationContextKey}")
            ->when(blank($pageTranslationContextKey) && $pageTranslationContext === PageTranslationContext::Table)->append('.table')
            ->when(blank($pageTranslationContextKey) && $pageTranslationContext === PageTranslationContext::Form)->append('.form')
            ->when(blank($pageTranslationContextKey) && $pageTranslationContext === PageTranslationContext::Infolist)->append('.infolist')
            ->when(blank($pageTranslationContextKey) && $pageTranslationContext === PageTranslationContext::Actions)->append('.actions')
            ->append(".{$key}");

        return $resourceClass::getTranslation($translationKey, $replace, $number, $allowNull);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return static::getTranslation('title', allowNull: true) ?? parent::getTitle($ownerRecord, $pageClass);
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return static::getTranslation('badge', allowNull: true) ?? parent::getBadge($ownerRecord, $pageClass);
    }

    public static function getBadgeTooltip(Model $ownerRecord, string $pageClass): ?string
    {
        return static::getTranslation('badge_tooltip', allowNull: true) ?? parent::getBadgeTooltip($ownerRecord, $pageClass);
    }

    public static function getModelLabel(): ?string
    {
        return static::getTranslation('model_label', allowNull: true) ?? parent::getModelLabel();
    }

    public static function getPluralModelLabel(): ?string
    {
        return static::getTranslation('plural_model_label', allowNull: true) ?? parent::getModelLabel();
    }
}
