<?php

namespace RalphJSmit\Filament\AutoTranslator\Concerns;

use Countable;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use RalphJSmit\Filament\AutoTranslator\Enums\PageTranslationContext;

/**
 * @mixin Page
 */
trait HasResourcePageTranslations
{
    public function getTitle(): string
    {
        return static::getTranslation('title', allowNull: true) ?? parent::getTitle();
    }

    public function getSubheading(): string|Htmlable|null
    {
        return static::getTranslation('subheading', allowNull: true) ?? parent::getSubheading();
    }

    // TODO: there is a conflicting return type between the `Resourcs\Pages\Page` class and all the other `Resources\Pages\*Record` classes.
    //    public function getBreadcrumb(): string
    //    {
    //        return static::getTranslation('breadcrumb', allowNull: true) ?? parent::getBreadcrumb();
    //    }

    public static function getNavigationLabel(): string
    {
        return static::getTranslation('navigation_label', allowNull: true) ?? parent::getNavigationLabel();
    }

    public static function getNavigationGroup(): ?string
    {
        return static::getTranslation('navigation_group', allowNull: true) ?? parent::getNavigationGroup();
    }

    public static function getTranslation(string $key, array $replace = [], Countable|float|int|null $number = null, bool $allowNull = false, ?PageTranslationContext $pageTranslationContext = null, ?string $pageTranslationContextKey = null): mixed
    {
        $resourcePageName = static::getResourcePageName();

        if (
            (
                (is_a(static::class, CreateRecord::class, true) && $resourcePageName === 'create')
                || (is_a(static::class, EditRecord::class, true) && $resourcePageName === 'edit')
                || (is_a(static::class, ViewRecord::class, true) && $resourcePageName === 'view')
            )
            && $pageTranslationContext === PageTranslationContext::Form
        ) {
            return static::getResource()::getTranslation($key, $replace, $number, $allowNull, $pageTranslationContext);
        }

        if (
            is_a(static::class, ViewRecord::class, true)
            && $resourcePageName === 'view'
            && $pageTranslationContext === PageTranslationContext::Infolist
        ) {
            return static::getResource()::getTranslation($key, $replace, $number, $allowNull, $pageTranslationContext);
        }

        if (
            is_a(static::class, ListRecords::class, true)
            && $resourcePageName === 'index'
            && $pageTranslationContext === PageTranslationContext::Table
        ) {
            return static::getResource()::getTranslation($key, $replace, $number, $allowNull, $pageTranslationContext);
        }

        $pageKey = str(static::class)
            ->classBasename()
            ->snake();

        if ($pageTranslationContextKey) {
            return static::getResource()::getTranslation("pages.{$pageKey}.{$pageTranslationContextKey}.{$key}", $replace, $number, $allowNull);
        }

        if ($pageTranslationContext) {
            return static::getResource()::getTranslation("pages.{$pageKey}.{$pageTranslationContext->value}.{$key}", $replace, $number, $allowNull);
        }

        return static::getResource()::getTranslation("pages.{$pageKey}.{$key}", $replace, $number, $allowNull);
    }
}
