<?php

namespace RalphJSmit\Filament\AutoTranslator\Concerns;

use Countable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Stringable;
use RalphJSmit\Filament\AutoTranslator\Enums\PageTranslationContext;
use RalphJSmit\Filament\AutoTranslator\FilamentAutoTranslator;

/**
 * @TODO: Implement page translation context.
 */
trait HasPageTranslations
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
            ->when($pageTranslationContext, fn (Stringable $str) => $str->append(".{$pageTranslationContext->value}"))
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

    public function getTitle(): string
    {
        return static::getTranslation('title', allowNull: true) ?? parent::getTitle();
    }

    public function getSubheading(): string | Htmlable | null
    {
        return static::getTranslation('subheading', allowNull: true) ?? parent::getSubheading();
    }

    public static function getNavigationLabel(): string
    {
        return static::getTranslation('navigation_label', allowNull: true) ?? parent::getNavigationLabel();
    }

    public static function getNavigationGroup(): ?string
    {
        return static::getTranslation('navigation_group', allowNull: true) ?? parent::getNavigationGroup();
    }
}
