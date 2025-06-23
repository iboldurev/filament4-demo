<?php

namespace RalphJSmit\Filament\AutoTranslator;

use BackedEnum;
use Closure;
use Countable;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Resources;
use Filament\Support\Contracts\HasDescription;
use Filament\Tables;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use RalphJSmit\Filament\AutoTranslator\Contracts\HasTranslations;
use RalphJSmit\Filament\AutoTranslator\Enums\ActionGroup;
use RalphJSmit\Filament\AutoTranslator\Enums\FormGroup;
use RalphJSmit\Filament\AutoTranslator\Enums\InfolistGroup;
use RalphJSmit\Filament\AutoTranslator\Enums\PageTranslationContext;
use RalphJSmit\Filament\AutoTranslator\Enums\TableGroup;

class AutoTranslator
{
    protected array $actionMethods = [
        'label' => false,
        'tooltip' => true,
        'badge' => true,
        'modalHeading' => true,
        'modalDescription' => true,
        'modalSubmitActionLabel' => true,
        'modalCancelActionLabel' => true,
        'successNotificationTitle' => true,
        'failureNotificationTitle' => true,
    ];

    /**
     * @var array<array-key, class-string<Forms\Components\Field>>
     */
    protected array $formFields = [
        Forms\Components\Field::class,
        Forms\Components\Fieldset::class,
        Forms\Components\Placeholder::class,
        Forms\Components\Section::class,
        Forms\Components\Tabs\Tab::class,
        Forms\Components\Wizard::class,
        Forms\Components\Wizard\Step::class,
    ];

    /**
     * Provide the form field methods to check and whether to allow null values.
     *
     * @var array<string, bool>
     */
    protected array $formFieldMethods = [
        'label' => false,
        'placeholder' => true,
        'helperText' => true,
        'hint' => true,
        // 'hintIconTooltip' => true, // Note: doesn't work, calling the `->hintIcon()` method overwrites the `->hintIconTooltip()`.
        'prefix' => true,
        'suffix' => true,
        'validationAttribute' => true,
        'addActionLabel' => true, // Repeater
        'addBetweenActionLabel' => true, // Repeater
        'heading' => false, // Section
        'description' => true, // Section, Wizard\Step
        'content' => false, // Placeholder
        'loadingMessage' => true, // Select
        'createOptionModalHeading' => true, // Select
        'editOptionModalHeading' => true, // Select
    ];

    /**
     * @var array<array-key, class-string<Infolists\Components\Entry>>
     */
    protected array $infolistEntries = [
        Infolists\Components\Entry::class,
        Infolists\Components\Fieldset::class,
        Infolists\Components\Section::class,
        Infolists\Components\Tabs\Tab::class,
    ];

    /**
     * Provide the form field methods to check and whether to allow null values.
     *
     * @var array<string, bool>
     */
    protected array $infolistEntryMethods = [
        'label' => false,
        'placeholder' => true,
        'helperText' => true,
        'hint' => true,
        // 'hintIconTooltip' => true,
        'prefix' => true,
        'suffix' => true,
        'default' => true, // For text-based infolist entries it can make sense to have a hard-coded default.
        'heading' => false, // Section
        'description' => true, // Section
    ];

    /**
     * Provide the table methods to check and whether to allow null values.
     *
     * @var array<string, bool>
     */
    protected array $tableMethods = [
        'searchPlaceholder' => true,
        'modelLabel' => true,
        'pluralModelLabel' => true,
        'heading' => true,
        'description' => true,
        'defaultSortOptionLabel' => true,
        'emptyStateHeading' => true,
        'emptyStateDescription' => true,
        'actionsColumnLabel' => true,
    ];

    /**
     * @var array<array-key, class-string<Tables\Columns\Column>>
     */
    protected array $tableColumns = [
        Tables\Columns\TextColumn::class,
        Tables\Columns\IconColumn::class,
        Tables\Columns\ImageColumn::class,
        Tables\Columns\ColorColumn::class,
        Tables\Columns\ToggleColumn::class,
        Tables\Columns\SelectColumn::class,
    ];

    /**
     * Provide the table column methods to check and whether to allow null values.
     *
     * @var array<string, bool>
     */
    protected array $tableColumnMethods = [
        'label' => false,
        'description' => true,
        'tooltip' => true,
        'prefix' => true,
        'suffix' => true,
        'placeholder' => true,
        'default' => true,
        'validationAttribute' => true,
    ];

    /**
     * Provide the table filter methods to check and whether to allow null values.
     *
     * @var array<string, bool>
     */
    protected array $tableFilterMethods = [
        'label' => false,
        'indicator' => true,
        'placeholder' => true,
        'trueLabel' => true,
        'falseLabel' => true,
    ];

    /**
     * Provide the table filter constraint methods to check and whether to allow null values.
     *
     * @var array<string, bool>
     */
    protected array $tableFilterConstraintMethods = [
        'label' => false,
        'attributeLabel' => true,
    ];

    /**
     * Provide the table summarizer methods to check and whether to allow null values.
     *
     * @var array<string, bool>
     */
    protected array $tableSummarizerMethods = [
        'label' => false,
        'prefix' => true,
        'suffix' => true,
    ];

    public function boot(): void
    {
        // Actions:
        $this->configureActions();

        // Forms:
        $this->configureFormActions();
        $this->configureFormFields();

        // Infolists:
        $this->configureInfolistActions();
        $this->configureInfolistFields();

        // Tables:
        $this->configureTables();
        $this->configureTableActions();
        $this->configureTableColumns();
        $this->configureTableColumnSummarizers();
        $this->configureTableFilters();
        $this->configureTableGroups();
    }

    protected function configureActions(): void
    {
        $actionMethods = $this->actionMethods;

        Actions\Action::configureUsing(static function (Actions\Action $action) use ($actionMethods) {
            foreach ($actionMethods as $method => $allowNull) {
                $action->{$method}(static function (Actions\Action $action) use ($method, $allowNull) {
                    $actionText = AutoTranslator::translateActionText($action, null, Str::snake($method), allowNull: true);

                    if ($actionText) {
                        return $actionText;
                    }

                    // Check if the action is a pre-built action, so we can then bind to the pre-built action's method.
                    if ($action::class !== Actions\Action::class) {
                        $prebuiltAction = app($action::class, ['name' => $action->getName()]);

                        invade($prebuiltAction)->setUp();

                        $default = invade($prebuiltAction)->{Str::camel($method)};

                        return $action->evaluate($default instanceof Closure ? Closure::bind($default, $action) : $default);
                    }

                    if ($allowNull) {
                        return $actionText;
                    }

                    return AutoTranslator::translateActionText($action, null, Str::snake($method), allowNull: false);
                });
            }
        }, isImportant: true);
    }

    protected function configureFormActions(): void
    {
        $actionMethods = $this->actionMethods;

        Forms\Components\Actions\Action::configureUsing(static function (Forms\Components\Actions\Action $action) use ($actionMethods) {
            foreach ($actionMethods as $method => $allowNull) {
                $action->{$method}(static function (Forms\Components\Actions\Action $action) use ($method, $allowNull) {
                    return AutoTranslator::translateFormText($action, FormGroup::Fields, Str::snake($method), allowNull: $allowNull);
                });
            }
        });
    }

    protected function configureFormFields(): void
    {
        // Keep this configuration before the `foreach` loop, to ensure that the configuration callbacks are called first and not overridden.
        Forms\Components\Fieldset::configureUsing(static function (Forms\Components\Fieldset $component) {
            // Calling the `getLabel()` method whilst it is a closure will cause an error about the $livewire component not being set.
            // If we were to not check for the closure, we might break existing forms in the app that aren't using autotranslator.
            if (invade($component)->label instanceof Closure) {
                return $component;
            }

            return $component->key($component->getLabel());
        });

        Forms\Components\Section::configureUsing(static function (Forms\Components\Section $component) {
            if (invade($component)->heading instanceof Closure) {
                return $component;
            }

            $heading = $component->getHeading();

            if (blank($heading)) {
                return;
            }

            return $component->key($heading);
        });

        Forms\Components\Tabs\Tab::configureUsing(static function (Forms\Components\Tabs\Tab $component) {
            if (invade($component)->label instanceof Closure) {
                return $component;
            }

            return $component->key($component->getLabel());
        });

        Forms\Components\Wizard\Step::configureUsing(static function (Forms\Components\Wizard\Step $component) {
            if (invade($component)->label instanceof Closure) {
                return $component;
            }

            return $component->key($component->getLabel());
        });

        foreach ($this->formFields as $formField) {
            if (! class_exists($formField)) {
                continue;
            }

            $formFieldMethods = $this->formFieldMethods;

            $formField::configureUsing(static function (Forms\Components\Component $component) use ($formFieldMethods) {
                foreach ($formFieldMethods as $method => $allowNull) {
                    if (method_exists($component, $method)) {
                        $component->{$method}(static function (Forms\Components\Component $component) use ($method, $allowNull) {
                            return AutoTranslator::translateFormText($component, FormGroup::Fields, Str::snake($method), allowNull: $allowNull);
                        });
                    }
                }
            });
        }

        Forms\Components\Radio::configureUsing(static function (Forms\Components\Radio $component) {
            $component
                ->options(static function (Forms\Components\Radio $component, string $model) {
                    /** @var class-string<Model> $cast */
                    $cast = (new $model)->getCasts()[$component->getName()] ?? null;

                    if (! $cast) {
                        return null;
                    }

                    if (str($cast)->startsWith(AsEnumCollection::class)) {
                        $cast = str($cast)->after(AsEnumCollection::class.':')->toString();
                    }

                    if (! is_subclass_of($cast, BackedEnum::class)) {
                        return null;
                    }

                    return $cast;
                })
                ->descriptions(static function (Forms\Components\Radio $component, ?string $model) {
                    if (! $model) {
                        return [];
                    }

                    /** @var class-string<Model> $cast */
                    $cast = (new $model)->getCasts()[$component->getName()] ?? null;

                    if (! $cast) {
                        return [];
                    }

                    if (str($cast)->startsWith(AsEnumCollection::class)) {
                        $cast = str($cast)->after(AsEnumCollection::class.':')->toString();
                    }

                    if (! is_subclass_of($cast, BackedEnum::class)) {
                        return [];
                    }

                    if (! is_a($cast, HasDescription::class, true)) {
                        return [];
                    }

                    return array_reduce($cast::cases(), function (array $carry, HasDescription&BackedEnum $case): array {
                        if (filled($description = $case->getDescription())) {
                            $carry[$case->value ?? $case->name] = $description;
                        }

                        return $carry;
                    }, []);
                });
        });

        Forms\Components\Select::configureUsing(static function (Forms\Components\Select $component) {
            $component->options(static function (Forms\Components\Select $component, string $model) {
                /** @var class-string<Model> $cast */
                $cast = (new $model)->getCasts()[$component->getName()] ?? null;

                if (! $cast) {
                    return null;
                }

                if (str($cast)->startsWith(AsEnumCollection::class)) {
                    $cast = str($cast)->after(AsEnumCollection::class.':')->toString();
                }

                if (! is_subclass_of($cast, BackedEnum::class)) {
                    return null;
                }

                return $cast;
            });
        });

        Forms\Components\ToggleButtons::configureUsing(static function (Forms\Components\ToggleButtons $component) {
            $component->options(static function (Forms\Components\ToggleButtons $component, string $model) {
                /** @var class-string<Model> $cast */
                $cast = (new $model)->getCasts()[$component->getName()] ?? null;

                if (! $cast) {
                    return null;
                }

                if (str($cast)->startsWith(AsEnumCollection::class)) {
                    $cast = str($cast)->after(AsEnumCollection::class.':')->toString();
                }

                if (! is_subclass_of($cast, BackedEnum::class)) {
                    return null;
                }

                return $cast;
            });
        });
    }

    protected function configureInfolistActions(): void
    {
        $actionMethods = $this->actionMethods;

        Infolists\Components\Actions\Action::configureUsing(static function (Infolists\Components\Actions\Action $action) use ($actionMethods) {
            foreach ($actionMethods as $method => $allowNull) {
                $action->{$method}(static function (Infolists\Components\Actions\Action $action) use ($method, $allowNull) {
                    return AutoTranslator::translateInfolistText($action, InfolistGroup::Entries, Str::snake($method), allowNull: $allowNull);
                });
            }
        });
    }

    protected function configureInfolistFields(): void
    {
        // Keep this configuration before the `foreach` loop, to ensure that the configuration callbacks are called first and not overridden.
        Infolists\Components\Fieldset::configureUsing(static function (Infolists\Components\Fieldset $component) {
            // Calling the `getHeading()` method whilst it is a closure will cause an error about the $livewire component not being set.
            // If we were to not check for the closure, we might break existing forms in the app that aren't using autotranslator.
            if (invade($component)->label instanceof Closure) {
                return $component;
            }

            return $component->key($component->getLabel());
        });

        Infolists\Components\Section::configureUsing(static function (Infolists\Components\Section $component) {
            if (invade($component)->heading instanceof Closure) {
                return $component;
            }

            $heading = $component->getHeading();

            if (blank($heading)) {
                return;
            }

            return $component->key($heading);
        });

        Infolists\Components\Tabs\Tab::configureUsing(static function (Infolists\Components\Tabs\Tab $component) {
            if (invade($component)->label instanceof Closure) {
                return $component;
            }

            return $component->key($component->getLabel());
        });

        foreach ($this->infolistEntries as $infolistEntry) {
            if (! class_exists($infolistEntry)) {
                continue;
            }

            $infolistEntryMethods = $this->infolistEntryMethods;

            $infolistEntry::configureUsing(static function (Infolists\Components\Component $component) use ($infolistEntryMethods) {
                foreach ($infolistEntryMethods as $method => $allowNull) {
                    if (method_exists($component, $method)) {
                        $component->{$method}(static function (Infolists\Components\Component $component) use ($method, $allowNull) {
                            return AutoTranslator::translateInfolistText($component, InfolistGroup::Entries, Str::snake($method), allowNull: $allowNull);
                        });
                    }
                }
            });
        }
    }

    protected function configureTables(): void
    {
        $tableMethods = $this->tableMethods;

        Tables\Table::configureUsing(static function (Tables\Table $table) use ($tableMethods) {
            foreach ($tableMethods as $method => $allowNull) {
                $table->{$method}(static function (Tables\Table $table) use ($allowNull, $method) {
                    return AutoTranslator::translateTableText($table, null, Str::snake($method), allowNull: $allowNull);
                });
            }
        }, isImportant: true);
    }

    protected function configureTableActions(): void
    {
        $actionMethods = $this->actionMethods;

        Tables\Actions\Action::configureUsing(static function (Tables\Actions\Action $action) use ($actionMethods) {
            foreach ($actionMethods as $method => $allowNull) {
                $action->{$method}(static function (Tables\Actions\Action $action) use ($method, $allowNull) {
                    $actionText = AutoTranslator::translateActionText($action, null, Str::snake($method), allowNull: true);

                    if ($actionText) {
                        return $actionText;
                    }

                    // Check if the action is a pre-built action, so we can then bind to the pre-built action's method.
                    if ($action::class !== Tables\Actions\Action::class) {
                        $prebuiltAction = app($action::class, ['name' => $action->getName()]);

                        invade($prebuiltAction)->setUp();

                        $default = invade($prebuiltAction)->{Str::camel($method)};

                        return $action->evaluate($default instanceof Closure ? Closure::bind($default, $action) : $default);
                    }

                    if ($allowNull) {
                        return $actionText;
                    }

                    return AutoTranslator::translateActionText($action, null, Str::snake($method), allowNull: false);
                });
            }
        }, isImportant: true);

        Tables\Actions\BulkAction::configureUsing(static function (Tables\Actions\BulkAction $action) use ($actionMethods) {
            foreach ($actionMethods as $method => $allowNull) {
                $action->{$method}(static function (Tables\Actions\BulkAction $action) use ($method, $allowNull) {
                    $actionText = AutoTranslator::translateActionText($action, null, Str::snake($method), allowNull: true);

                    if ($actionText) {
                        return $actionText;
                    }

                    // Check if the action is a pre-built action, so we can then bind to the pre-built action's method.
                    if ($action::class !== Tables\Actions\BulkAction::class) {
                        $prebuiltBulkAction = app($action::class, ['name' => $action->getName()]);

                        invade($prebuiltBulkAction)->setUp();

                        $default = invade($prebuiltBulkAction)->{Str::camel($method)};

                        return $action->evaluate($default instanceof Closure ? Closure::bind($default, $action) : $default);
                    }

                    if ($allowNull) {
                        return $actionText;
                    }

                    return AutoTranslator::translateActionText($action, null, Str::snake($method), allowNull: false);
                });
            }
        }, isImportant: true);
    }

    protected function configureTableColumns(): void
    {
        Tables\Columns\ColumnGroup::configureUsing(static function (Tables\Columns\ColumnGroup $component) {
            if (invade($component)->label instanceof Closure) {
                return $component;
            }

            $label = $component->getLabel();

            if (blank($label)) {
                return;
            }

            return $component->translationKey($label);
        });

        $tableColumnMethods = $this->tableColumnMethods;

        foreach ($this->tableColumns as $tableColumn) {
            if (! class_exists($tableColumn)) {
                continue;
            }

            $tableColumn::configureUsing(static function (Tables\Columns\Column|Tables\Columns\ColumnGroup $column) use ($tableColumnMethods) {
                foreach ($tableColumnMethods as $method => $allowNull) {
                    if (method_exists($column, $method)) {
                        $column->{$method}(static function (Tables\Columns\Column $column) use ($method, $allowNull) {
                            return AutoTranslator::translateTableText($column, TableGroup::Columns, Str::snake($method), allowNull: $allowNull);
                        });
                    }
                }
            });
        }

        Tables\Columns\ColumnGroup::configureUsing(static function (Tables\Columns\ColumnGroup $group) use ($tableColumnMethods) {
            foreach ($tableColumnMethods as $method => $allowNull) {
                if (method_exists($group, $method)) {
                    $group->{$method}(static function (Tables\Columns\ColumnGroup $column) use ($method, $allowNull) {
                        return AutoTranslator::translateTableText($column, TableGroup::Columns, Str::snake($method), allowNull: $allowNull);
                    });
                }
            }
        });
    }

    protected function configureTableFilters(): void
    {
        $tableFilterMethods = $this->tableFilterMethods;

        Tables\Filters\BaseFilter::configureUsing(static function (Tables\Filters\BaseFilter $filter) use ($tableFilterMethods) {
            foreach ($tableFilterMethods as $method => $allowNull) {
                if (method_exists($filter, $method)) {
                    $filter->{$method}(static function (Tables\Filters\BaseFilter $filter) use ($method, $allowNull) {
                        $filterText = AutoTranslator::translateTableText($filter, TableGroup::Filters, Str::snake($method), allowNull: true);

                        if ($filterText) {
                            return $filterText;
                        }

                        $shouldRetrievePrebuiltTranslation = ($filter instanceof Tables\Filters\SelectFilter && $method === 'placeholder')
                            || ($filter instanceof Tables\Filters\TernaryFilter && in_array($method, ['trueLabel', 'falseLabel', 'placeholder']))
                            || $filter instanceof Tables\Filters\TrashedFilter;

                        // Check if the action is a pre-built action, so we can then bind to the pre-built action's method.
                        if ($shouldRetrievePrebuiltTranslation) {
                            $prebuiltFilter = app($filter::class, ['name' => $filter->getName()]);

                            invade($prebuiltFilter)->setUp();

                            $default = invade($prebuiltFilter)->{Str::camel($method)};

                            return $filter->evaluate($default instanceof Closure ? Closure::bind($default, $filter) : $default);
                        }

                        if ($allowNull) {
                            return $filterText;
                        }

                        return AutoTranslator::translateTableText($filter, TableGroup::Filters, Str::snake($method), allowNull: false);
                    });
                }
            }
        }, isImportant: true);

        Tables\Filters\SelectFilter::configureUsing(static function (Tables\Filters\SelectFilter $filter) {
            $filter->options(static function (Tables\Table $table, Tables\Filters\SelectFilter $filter) {
                $model = $table->getModel();

                /** @var class-string<Model> $cast */
                $cast = (new $model)->getCasts()[$filter->getName()] ?? null;

                if (! $cast) {
                    return [];
                }

                if (str($cast)->startsWith(AsEnumCollection::class)) {
                    $cast = str($cast)->after(AsEnumCollection::class.':')->toString();
                }

                if (! is_subclass_of($cast, BackedEnum::class)) {
                    return [];
                }

                return $cast;
            });
        });

        $tableFilterConstraintMethods = $this->tableFilterConstraintMethods;

        Tables\Filters\QueryBuilder\Constraints\Constraint::configureUsing(static function (Tables\Filters\QueryBuilder\Constraints\Constraint $constraint) use ($tableFilterConstraintMethods) {
            foreach ($tableFilterConstraintMethods as $method => $allowNull) {
                if (method_exists($constraint, $method)) {
                    $constraint->{$method}(static function (Tables\Filters\QueryBuilder\Constraints\Constraint $constraint) use ($method, $allowNull) {
                        /** @var Tables\Filters\QueryBuilder $filter */
                        $filter = $constraint->getFilter();

                        return AutoTranslator::translateTableText($filter, TableGroup::Filters, "constraints.{$constraint->getName()}.".Str::snake($method), allowNull: $allowNull);
                    });
                }
            }
        }, isImportant: true);
    }

    protected function configureTableGroups(): void
    {
        Tables\Grouping\Group::configureUsing(static function (Tables\Grouping\Group $group) {
            $group->label(static function (Tables\Grouping\Group $group) {
                return AutoTranslator::translateTableText($group, TableGroup::Groups, 'label');
            });
        }, isImportant: true);
    }

    protected function configureTableColumnSummarizers(): void
    {
        $tableSummarizerMethods = $this->tableSummarizerMethods;

        Tables\Columns\Summarizers\Summarizer::configureUsing(static function (Tables\Columns\Summarizers\Summarizer $summarizer) use ($tableSummarizerMethods) {
            foreach ($tableSummarizerMethods as $method => $allowNull) {
                $summarizer->{$method}(static function (Tables\Columns\Summarizers\Summarizer $summarizer) use ($method, $allowNull) {
                    return AutoTranslator::translateTableText($summarizer, TableGroup::Filters, Str::snake($method), allowNull: $allowNull);
                });
            }
        }, isImportant: true);

        // Note: Filament groups do not support closures...
        //        Tables\Grouping\Group::configureUsing(static function (Tables\Grouping\Group $group) {
        //            $group
        //                ->label(static function (Tables\Grouping\Group $group) {
        //                    return AutoTranslator::translateTableText($group, TableGroup::Groups, 'label');
        //                })
        //                ->indicator(static function (Tables\Grouping\Group $group) {
        //                    return AutoTranslator::translateTableText($group, TableGroup::Groups, 'indicator', allowNull: true);
        //                });
        //        }, isImportant: true);
    }

    protected static function normalizeName(string $name, array $namespace = []): string
    {
        if ($namespace) {
            $prefix = implode('.', $namespace).'.';
        } else {
            $prefix = '';
        }

        return str($name)
            ->whenStartsWith('data.', static function (Stringable $normalizedName) {
                return $normalizedName->after('data.');
            })
            ->replace('.', '->', $name)
            ->prepend($prefix);
    }

    protected static function translateAbsoluteText(string $topLevel, string $key, array $replace = [], Countable|float|int|null $number = null, bool $allowNull = false): ?string
    {
        if ($topLevel) {
            $translationKey = "{$topLevel}.{$key}";
        } else {
            $translationKey = $key;
        }

        if (! app('translator')->has($translationKey) && ($allowNull || app()->isProduction())) {
            return null;
        }

        if ($number !== null) {
            return trans_choice($translationKey, $number, $replace);
        }

        return __($translationKey, $replace);
    }

    public static function translateActionText(Tables\Actions\Action|Tables\Actions\BulkAction|Actions\Action $actionComponent, ?ActionGroup $group, string $key, array $replace = [], Countable|float|int|null $number = null, bool $allowNull = false): ?string
    {
        $livewire = $actionComponent->getLivewire();

        if (! $livewire instanceof HasTranslations) {
            return null;
        }

        if (
            $actionComponent->isTranslationKeyAbsolute()
            && ($translationKey = $actionComponent->getTranslationKey()) !== null
        ) {
            if ($group) {
                $key = "{$group->value}.{$key}";
            }

            return static::translateAbsoluteText($translationKey, $key, $replace, $number, $allowNull);
        }

        $normalizedName = AutoTranslator::normalizeName($actionComponent->getTranslationKey() ?? $actionComponent->getName());

        if ($actionComponent instanceof Tables\Actions\BulkAction) {
            // Bulk actions are not used in nesting, so we can re-target them right away.
            return AutoTranslator::translateTableText($actionComponent, TableGroup::BulkActions, $group ? "{$group->value}.{$key}" : $key, $replace, $number, $allowNull);
        }

        if ($actionComponent instanceof Tables\Actions\Action) {
            // [ 0 => "view", 1 => "edit", 2 => "create_something" ]
            $mountedTableActions = collect(invade($livewire)->mountedTableActions);

            if (
                $mountedTableActions->isNotEmpty()
                && ! ($livewire->getTable()->getFlatActions()[$actionComponent->getName()] ?? null)
            ) {
                // Search on key "create_something", get the key "2"
                $actionComponentNestingIndex = $mountedTableActions->search($actionComponent->getName());

                if ($actionComponentNestingIndex === false) {
                    // Sometimes, when working with native actions on a Livewire component, the action might be
                    // called `view_booking`, the method `viewBooking()` and the mounted action "viewBooking".
                    $actionComponentNestingIndex = $mountedTableActions->search(Str::camel($actionComponent->getName()));
                }

                if ($actionComponentNestingIndex === false) {
                    // If the action is not found, it could also be that the action is not yet mounted,
                    // but already visible in the footer. For example, the `label` key of an action is
                    // already retrieved before the action is opened, and that we handle as well.
                    $parentMountedTableAction = $livewire->getTable()->getFlatActions()[$mountedTableActions->shift()] ?? null;

                    while ($parentMountedTableAction && $mountedTableActions->isNotEmpty() && $parentMountedTableAction->getExtraModalFooterActions()) {
                        $parentMountedTableActionName = $mountedTableActions->shift();

                        $parentMountedTableAction = collect($parentMountedTableAction->getExtraModalFooterActions())
                            ->first(fn (Tables\Actions\Action $action) => ($actionName = $action->getName()) === $parentMountedTableActionName || $actionName === Str::camel($parentMountedTableActionName));
                    }

                    if ($parentMountedTableAction) {
                        return AutoTranslator::translateActionText($parentMountedTableAction, null, $group ? "extra_modal_footer_actions.{$normalizedName}.{$group->value}.{$key}" : "extra_modal_footer_actions.{$normalizedName}.{$key}", $replace, $number, $allowNull);
                    }
                }

                // Remove all actions that are before the current action index (it goes from first to last)
                $mountedTableActions = $mountedTableActions
                    ->filter(fn (string $actionName, int $key) => $key < $actionComponentNestingIndex)
                    ->values();

                if ($mountedTableActions->isNotEmpty()) {
                    $parentMountedTableAction = $livewire->getTable()->getFlatActions()[$mountedTableActions->shift()] ?? null;

                    while ($parentMountedTableAction && $mountedTableActions->isNotEmpty() && $parentMountedTableAction->getExtraModalFooterActions()) {
                        $parentMountedTableActionName = $mountedTableActions->shift();

                        $parentMountedTableAction = collect($parentMountedTableAction->getExtraModalFooterActions())
                            ->first(fn (Tables\Actions\Action $action) => $action->getName() === $parentMountedTableActionName);
                    }

                    if ($parentMountedTableAction) {
                        return AutoTranslator::translateActionText($parentMountedTableAction, null, $group ? "extra_modal_footer_actions.{$normalizedName}.{$group->value}.{$key}" : "extra_modal_footer_actions.{$normalizedName}.{$key}", $replace, $number, $allowNull);
                    }
                }
            }

            return AutoTranslator::translateTableText($actionComponent, TableGroup::Actions, $group ? "{$group->value}.{$key}" : $key, $replace, $number, $allowNull);
        }

        if ($actionComponent instanceof Actions\Action) {
            // [ 0 => "view", 1 => "edit", 2 => "create_something" ]
            $mountedActions = collect(invade($livewire)->mountedActions);

            if ($mountedActions->isNotEmpty() && (! $livewire->getAction($actionComponent->getName()))) {
                // Search on key "create_something", get the key "2"
                $actionComponentNestingIndex = $mountedActions->search($actionComponent->getName());

                if ($actionComponentNestingIndex === false) {
                    // Sometimes, when working with native actions on a Livewire component, the action might be
                    // called `view_booking`, the method `viewBooking()` and the mounted action "viewBooking".
                    $actionComponentNestingIndex = $mountedActions->search(Str::camel($actionComponent->getName()));
                }

                if ($actionComponentNestingIndex === false) {
                    // If the action is not found, it could also be that the action is not yet mounted,
                    // but already visible in the footer. For example, the `label` key of an action is
                    // already retrieved before the action is opened, and that we handle as well.
                    $parentMountedAction = $livewire->getAction($mountedActions->shift());

                    while ($parentMountedAction && $mountedActions->isNotEmpty() && $parentMountedAction->getExtraModalFooterActions()) {
                        $parentMountedActionName = $mountedActions->shift();

                        $parentMountedAction = collect($parentMountedAction->getExtraModalFooterActions())
                            ->first(fn (Actions\Action $action) => ($actionName = $action->getName()) === $parentMountedActionName || $actionName === Str::camel($parentMountedActionName));
                    }

                    if ($parentMountedAction) {
                        return AutoTranslator::translateActionText($parentMountedAction, null, $group ? "extra_modal_footer_actions.{$normalizedName}.{$group->value}.{$key}" : "extra_modal_footer_actions.{$normalizedName}.{$key}", $replace, $number, $allowNull);
                    }
                }

                // Remove all actions that are before the current action index (it goes from first to last)
                $mountedActions = $mountedActions
                    ->filter(fn (string $actionName, int $key) => $key < $actionComponentNestingIndex)
                    ->values();

                if ($mountedActions->isNotEmpty()) {
                    $parentMountedAction = $livewire->getAction($mountedActions->shift());

                    while ($parentMountedAction && $mountedActions->isNotEmpty() && $parentMountedAction->getExtraModalFooterActions()) {
                        $parentMountedActionName = $mountedActions->shift();

                        $parentMountedAction = collect($parentMountedAction->getExtraModalFooterActions())
                            ->first(fn (Actions\Action $action) => $action->getName() === $parentMountedActionName);
                    }

                    if ($parentMountedAction) {
                        return AutoTranslator::translateActionText($parentMountedAction, null, $group ? "extra_modal_footer_actions.{$normalizedName}.{$group->value}.{$key}" : "extra_modal_footer_actions.{$normalizedName}.{$key}", $replace, $number, $allowNull);
                    }
                }
            }
        }

        return $livewire::getTranslation($group ? "{$normalizedName}.{$group->value}.{$key}" : "{$normalizedName}.{$key}", replace: $replace, number: $number, allowNull: $allowNull, pageTranslationContext: PageTranslationContext::Actions);
    }

    public static function translateFormText(Forms\Components\Component|Forms\Components\Actions\Action|Forms\ComponentContainer $formComponent, ?FormGroup $group, string $key, array $replace = [], Countable|float|int|null $number = null, bool $allowNull = false): ?string
    {
        $livewire = $formComponent->getLivewire();

        if (! $livewire instanceof HasTranslations) {
            return null;
        }

        if (
            $formComponent->isTranslationKeyAbsolute()
            && ($translationKey = $formComponent->getTranslationKey()) !== null
        ) {
            if ($group) {
                $key = "{$group->value}.{$key}";
            }

            return static::translateAbsoluteText($translationKey, $key, $replace, $number, $allowNull);
        }

        $normalizedName = match (true) {
            $formComponent instanceof Forms\Components\Field => AutoTranslator::normalizeName($formComponent->getTranslationKey() ?? ($formComponent->getKey() !== $formComponent->getStatePath() ? $formComponent->getKey() : $formComponent->getName())),
            $formComponent instanceof Forms\Components\Placeholder => AutoTranslator::normalizeName($formComponent->getTranslationKey() ?? $formComponent->getKey() ?? $formComponent->getName()),
            $formComponent instanceof Forms\Components\Actions\Action => AutoTranslator::normalizeName($formComponent->getTranslationKey() ?? $formComponent->getName()),
            $formComponent instanceof Forms\Components\Tabs => AutoTranslator::normalizeName($formComponent->getTranslationKey() ?? $formComponent->getKey() ?? $formComponent->getId() ?? 'tabs'),
            $formComponent instanceof Forms\Components\Tabs\Tab => AutoTranslator::normalizeName($formComponent->getTranslationKey() ?? $formComponent->getKey()),
            $formComponent instanceof Forms\Components\Wizard => AutoTranslator::normalizeName($formComponent->getTranslationKey() ?? $formComponent->getKey() ?? $formComponent->getId() ?? 'wizard'),
            $formComponent instanceof Forms\Components\Wizard\Step => AutoTranslator::normalizeName($formComponent->getTranslationKey() ?? $formComponent->getKey()),
            $formComponent instanceof Forms\Components\Builder\Block => AutoTranslator::normalizeName($formComponent->getTranslationKey() ?? $formComponent->getKey() ?? $formComponent->getName()),
            $formComponent instanceof Forms\ComponentContainer => null,
            default => ($identifier = $formComponent->getTranslationKey() ?? $formComponent->getKey() ?? $formComponent->getId()) ? AutoTranslator::normalizeName($identifier) : null,
        };

        if (! $formComponent instanceof Forms\Form && $formComponent instanceof Forms\ComponentContainer) {
            $parentComponent = $formComponent->getParentComponent();

            if ($parentComponent instanceof Forms\Components\Fieldset) {
                return AutoTranslator::translateFormText($parentComponent, $group, "schema.{$key}", $replace, $number, allowNull: $allowNull);
            }

            if ($parentComponent instanceof Forms\Components\Section) {
                return AutoTranslator::translateFormText($parentComponent, $group, "schema.{$key}", $replace, $number, allowNull: $allowNull);
            }

            if ($parentComponent instanceof Forms\Components\Repeater) {
                return AutoTranslator::translateFormText($parentComponent, $group, "schema.{$key}", $replace, $number, allowNull: $allowNull);
            }

            if ($parentComponent instanceof Forms\Components\Tabs) {
                return AutoTranslator::translateFormText($parentComponent, $group, "tabs.{$key}", $replace, $number, allowNull: $allowNull);
            }

            if ($parentComponent instanceof Forms\Components\Tabs\Tab) {
                return AutoTranslator::translateFormText($parentComponent, $group, "schema.{$key}", $replace, $number, allowNull: $allowNull);
            }

            if ($parentComponent instanceof Forms\Components\Wizard\Step) {
                return AutoTranslator::translateFormText($parentComponent, $group, "schema.{$key}", $replace, $number, allowNull: $allowNull);
            }

            if ($parentComponent instanceof Forms\Components\Builder\Block) {
                return AutoTranslator::translateFormText($parentComponent, $group, "schema.{$key}", $replace, $number, allowNull: $allowNull);
            }

            return AutoTranslator::translateFormText($parentComponent, $group, $key, $replace, $number, allowNull: $allowNull);
        }

        if ($formComponent instanceof Forms\Components\Actions\Action) {
            $component = $formComponent->getComponent();

            if ($component instanceof Forms\Components\Actions || $component instanceof Forms\Components\Actions\ActionContainer) {
                return AutoTranslator::translateFormText($component, $group, "actions.{$normalizedName}.{$key}", $replace, $number, allowNull: $allowNull);
            } else {
                $formActionTypeNamespace = null;

                $formActionTypes = [
                    'prefix_actions' => Forms\Components\Contracts\HasAffixActions::class,
                    'suffix_actions' => Forms\Components\Contracts\HasAffixActions::class,
                    'extra_item_actions' => Forms\Components\Contracts\HasExtraItemActions::class,
                    'footer_actions' => Forms\Components\Contracts\HasFooterActions::class,
                    'header_actions' => Forms\Components\Contracts\HasHeaderActions::class,
                    'hint_actions' => Forms\Components\Contracts\HasHintActions::class,
                ];

                foreach ($formActionTypes as $type => $interface) {
                    if (
                        $component instanceof $interface
                        && collect($component->{'get'.Str::studly($type)}())
                            ->map(static function (Forms\Components\Actions\Action $action) {
                                return $action->getName();
                            })
                            ->contains($formComponent->getName())
                    ) {
                        $formActionTypeNamespace = $type;
                    }
                }

                // If no form action type namespace was found, default to the `actions` namespace.
                // For example, in a custom component where actions are added, this will just
                // generate a key like "component_name.actions.action_name.action_key" fine.
                $formActionTypeNamespace ??= 'actions';

                return AutoTranslator::translateFormText($component, $group, "{$formActionTypeNamespace}.{$normalizedName}.{$key}", $replace, $number, allowNull: $allowNull);
            }
        }

        if ($formComponent instanceof Forms\Components\Actions || $formComponent instanceof Forms\Components\Actions\ActionContainer) {
            return AutoTranslator::translateFormText($formComponent->getContainer(), $group, $key, $replace, $number, allowNull: $allowNull);
        }

        if ($formComponent instanceof Forms\Components\Tabs\Tab) {
            return AutoTranslator::translateFormText($formComponent->getContainer(), $group, "{$normalizedName}.{$key}", $replace, $number, allowNull: $allowNull);
        }

        if ($formComponent instanceof Forms\Components\Wizard) {
            return AutoTranslator::translateFormText($formComponent->getContainer(), $group, "{$normalizedName}.{$key}", $replace, $number, allowNull: $allowNull);
        }

        if ($formComponent instanceof Forms\Components\Wizard\Step) {
            return AutoTranslator::translateFormText($formComponent->getContainer(), $group, "steps.{$normalizedName}.{$key}", $replace, $number, allowNull: $allowNull);
        }

        if ($formComponent instanceof Forms\Components\Builder\Block) {
            return AutoTranslator::translateFormText($formComponent->getContainer(), $group, "blocks.{$normalizedName}.{$key}", $replace, $number, allowNull: $allowNull);
        }

        if ($formComponent instanceof Forms\Components\Group || $formComponent instanceof Forms\Components\Grid) {
            if (
                $livewire instanceof Tables\Contracts\HasTable
                && ($formComponentKey = $formComponent->getKey())
                && ($filter = $livewire->getTable()->getFilter($formComponentKey))
            ) {
                return AutoTranslator::translateTableText($filter, TableGroup::Filters, $group ? "form.{$group->value}.{$key}" : "form.{$key}", $replace, $number, $allowNull);
            }

            $container = $formComponent->getContainer();

            if (! $container instanceof Forms\Form) {
                return AutoTranslator::translateFormText($container, $group, $normalizedName ? "{$normalizedName}.{$key}" : $key, $replace, $number, allowNull: $allowNull);
            }
        }

        /** @var Forms\Form|Forms\ComponentContainer $container */
        $container = $formComponent instanceof Forms\Form ? $formComponent : $formComponent->getContainer();

        if ($container !== $formComponent) {
            return AutoTranslator::translateFormText($container, $group, $normalizedName ? "{$normalizedName}.{$key}" : $key, $replace, $number, allowNull: $allowNull);
        }

        $lastOperation = $container->getOperation();

        if (Str::contains($lastOperation, '.')) {
            $lastOperation = Str::afterLast($lastOperation, '.');
        }

        $key = match (true) {
            $normalizedName && $group => "{$group->value}.{$normalizedName}.{$key}",
            $normalizedName && ! $group => "{$normalizedName}.{$key}",
            ! $normalizedName && $group => "{$group->value}.{$key}",
            ! $normalizedName && ! $group => $key,
        };

        $mountedAction = null;

        if ($livewire instanceof Tables\Contracts\HasTable || $livewire instanceof Actions\Contracts\HasActions) {
            if ($livewire instanceof Tables\Contracts\HasTable) {
                $mountedAction = $livewire->getMountedTableAction() ?? $livewire->getMountedTableBulkAction();
            }

            if ($livewire instanceof Actions\Contracts\HasActions) {
                $mountedAction ??= $livewire->getMountedAction();
            }
        }

        if (in_array($lastOperation, ['createOption', 'editOption'])) {
            // By default, the last nesting index is used...
            /** @var null|Forms\Components\Actions\Action $mountedFormComponentAction */
            $mountedFormComponentAction = $livewire->getMountedFormComponentAction(
                array_search($lastOperation, $livewire->mountedFormComponentActions)
            );

            if ($mountedFormComponentAction->getName() === $lastOperation) {
                /** @var Forms\Components\Select $component */
                $component = $mountedFormComponentAction->getComponent();
                $createOptionActionForm = invade($component)->createOptionActionForm;
                $editOptionActionForm = invade($component)->editOptionActionForm;

                $namespace = match (true) {
                    $createOptionActionForm === $editOptionActionForm => 'manage_option_form',
                    $lastOperation === 'createOption' && $createOptionActionForm !== $editOptionActionForm => 'create_option_form',
                    $lastOperation === 'editOption' && $createOptionActionForm !== $editOptionActionForm => 'edit_option_form',
                };

                return AutoTranslator::translateFormText($component, FormGroup::Fields, "{$namespace}.{$key}", $replace, $number, $allowNull);
            }
        }

        if (
            // Keep before the `HasTable` or `HasActions` check, because mounted form actions can be nested
            // and we don't want the still mounted parent action to take precedence for the translations.
            ($mountedFormComponentAction = $livewire->getMountedFormComponentAction())
            && $lastOperation === $mountedFormComponentAction->getName()
        ) {
            /** @var $mountedFormComponentAction Forms\Components\Actions\Action */
            return AutoTranslator::translateFormText($mountedFormComponentAction, $group, "form.{$key}", $replace, $number, allowNull: $allowNull);
        }

        if (
            $livewire instanceof Infolists\Contracts\HasInfolists
            // Keep before the `HasTable` or `HasActions` check, because mounted form actions can be nested
            // and we don't want the still mounted parent action to take precedence for the translations.
            && ($mountedInfolistAction = $livewire->getMountedInfolistAction())
            && $lastOperation === $mountedInfolistAction->getName()
        ) {

            /** @var $mountedInfolistAction Infolists\Components\Actions\Action */
            return AutoTranslator::translateInfolistText($mountedInfolistAction, InfolistGroup::Entries, "form.{$key}", $replace, $number, allowNull: $allowNull);
        }

        if (
            (
                ($livewire instanceof Resources\Pages\CreateRecord && $lastOperation === 'create')
                || ($livewire instanceof Resources\Pages\ListRecords && $lastOperation === 'create')
                || ($livewire instanceof Resources\Pages\ManageRecords && $lastOperation === 'create')
                || ($livewire instanceof Resources\Pages\EditRecord && $lastOperation === 'edit')
                || ($livewire instanceof Resources\Pages\ListRecords && $lastOperation === 'edit')
                || ($livewire instanceof Resources\Pages\ManageRecords && $lastOperation === 'edit')
                || ($livewire instanceof Resources\Pages\ListRecords && $lastOperation === 'view')
                || ($livewire instanceof Resources\Pages\ManageRecords && $lastOperation === 'view')
            )
            && class_implements($resource = $livewire::getResource(), HasTranslations::class)
        ) {
            /** @var class-string<HasTranslations> $resource */
            return $resource::getTranslation($key, $replace, $number, allowNull: $allowNull, pageTranslationContext: PageTranslationContext::Form);
        }

        if (
            (
                ($livewire instanceof Resources\Pages\ManageRelatedRecords && $lastOperation === 'create')
                || ($livewire instanceof Resources\Pages\ManageRelatedRecords && $lastOperation === 'edit')
                || ($livewire instanceof Resources\Pages\ManageRelatedRecords && $lastOperation === 'view')
            )
            && class_implements($livewire, HasTranslations::class)
        ) {
            if (
                ! $mountedAction
                // If you are on a `ManageRelatedRecords` page, only the table actions will inherit the form automatically.
                // If the current action is not a table action, we will not inherit the main page form, but from the action.
                || $mountedAction instanceof Tables\Actions\Action
            ) {
                /** @var class-string<HasTranslations> $livewire */
                return $livewire::getTranslation($key, $replace, $number, allowNull: $allowNull, pageTranslationContext: PageTranslationContext::Form);
            }
        }

        if (
            (
                ($livewire instanceof Resources\RelationManagers\RelationManager && $lastOperation === 'create')
                || ($livewire instanceof Resources\RelationManagers\RelationManager && $lastOperation === 'edit')
                || ($livewire instanceof Resources\RelationManagers\RelationManager && $lastOperation === 'view')
            )
            && class_implements($livewire, HasTranslations::class)
        ) {
            /** @var class-string<HasTranslations> $livewire */
            return $livewire::getTranslation($key, $replace, $number, allowNull: $allowNull, pageTranslationContext: PageTranslationContext::Form);
        }

        if (
            $mountedAction
            && invade($mountedAction)->form
        ) {
            $mountedActionFormComponentsToCompare = match (true) {
                $mountedAction instanceof Actions\Action => $livewire->getMountedActionForm($mountedAction)->getComponents(),
                $mountedAction instanceof Tables\Actions\Action => $livewire->getMountedTableActionForm($mountedAction)->getComponents(),
                $mountedAction instanceof Tables\Actions\BulkAction => $livewire->getMountedTableBulkActionForm($mountedAction)->getComponents(),
            };

            $containerComponentsToCompare = $container->getComponents();

            if (
                ($mountedActionFormComponentsToCompare !== $containerComponentsToCompare)
                && count($mountedActionFormComponentsToCompare) === 1
                && count($containerComponentsToCompare) === 1
                && $mountedActionFormComponentsToCompare[0] instanceof Forms\Components\Wizard
                && $containerComponentsToCompare[0] instanceof Forms\Components\Wizard
            ) {
                // If the action directly contains a wizard, apparently the wizard is cloned,
                // but the child components stay unique, which we can then use to match.

                $mountedActionFormComponentsToCompareChildComponents = $mountedActionFormComponentsToCompare[0]->getChildComponents();
                $containerComponentsToCompareChildComponents = $containerComponentsToCompare[0]->getChildComponents();

                if ($mountedActionFormComponentsToCompareChildComponents === $containerComponentsToCompareChildComponents) {
                    $mountedActionFormComponentsToCompare = $mountedActionFormComponentsToCompareChildComponents;
                    $containerComponentsToCompare = $containerComponentsToCompareChildComponents;
                } else {
                    // If the wizard components do not match, we will do one final test to compare by state path
                    // instead of the container. This will return something like `mountedActionsData.0` as path.
                    $mountedActionFormComponentsToCompare = $mountedActionFormComponentsToCompare[0]->getContainer()->getStatePath();
                    $containerComponentsToCompare = $containerComponentsToCompare[0]->getContainer()->getStatePath();
                }
            }

            if ($mountedActionFormComponentsToCompare === $containerComponentsToCompare) {
                // If there is e.g. a form action at the bottom of the page to submit a form with the name of "create",
                // but which does not actually have a form itself, then we do not want to translation the form text
                // via that mounted action (that in essence only functions as a simple submit button on the page).
                return AutoTranslator::translateActionText($mountedAction, ActionGroup::Form, $key, $replace, $number, allowNull: $allowNull);
            }
        }

        // Determine on which form the translation should be based on.
        /** @var array<array-key|string, string|Forms\Form> $forms */
        $forms = [
            ...invade($livewire)->getCachedForms(),
            ...invade($livewire)->getTraitForms(),
        ];

        $containerName = 'form';

        if (
            $forms !== []
            && (
                (array_is_list($forms) && $forms !== ['form'])
                || (array_keys($forms) !== ['form'])
            )
        ) {
            foreach ($forms as $formKey => $form) {
                $formName = is_string($form) ? $form : $formKey;
                $form = is_string($form) ? $livewire->getForm($form) : $form;

                if ($form === $container) {
                    $containerName = $formName;
                }
            }
        }

        // We reached the bottom we can go (`$container instanceof Forms\Form`)..
        return $livewire::getTranslation(
            key: $key,
            replace: $replace,
            number: $number,
            allowNull: $allowNull,
            pageTranslationContext: PageTranslationContext::Form,
            pageTranslationContextKey: $containerName !== 'form' ? Str::snake($containerName) : null
        );
    }

    public static function translateInfolistText(Infolists\Components\Component|Infolists\Components\Actions\Action|Infolists\ComponentContainer $infolistComponent, ?InfolistGroup $group, string $key, array $replace = [], Countable|float|int|null $number = null, bool $allowNull = false): ?string
    {
        $livewire = $infolistComponent->getLivewire();

        if (! $livewire instanceof HasTranslations) {
            return null;
        }

        if (
            $infolistComponent->isTranslationKeyAbsolute()
            && ($translationKey = $infolistComponent->getTranslationKey()) !== null
        ) {
            if ($group) {
                $key = "{$group->value}.{$key}";
            }

            return static::translateAbsoluteText($translationKey, $key, $replace, $number, $allowNull);
        }

        $normalizedName = match (true) {
            $infolistComponent instanceof Infolists\Components\Entry => AutoTranslator::normalizeName($infolistComponent->getTranslationKey() ?? ($infolistComponent->getKey() !== $infolistComponent->getStatePath() ? $infolistComponent->getKey() : $infolistComponent->getName())),
            $infolistComponent instanceof Infolists\Components\Actions\Action => AutoTranslator::normalizeName($infolistComponent->getTranslationKey() ?? $infolistComponent->getName()),
            $infolistComponent instanceof Infolists\Components\Tabs => AutoTranslator::normalizeName($infolistComponent->getTranslationKey() ?? $infolistComponent->getKey() ?? $infolistComponent->getId() ?? 'tabs'),
            $infolistComponent instanceof Infolists\Components\Tabs\Tab => AutoTranslator::normalizeName($infolistComponent->getTranslationKey() ?? $infolistComponent->getKey()),
            $infolistComponent instanceof Infolists\ComponentContainer => null,
            default => ($identifier = $infolistComponent->getTranslationKey() ?? $infolistComponent->getKey() ?? $infolistComponent->getId()) ? AutoTranslator::normalizeName($identifier) : null,
        };

        if (! $infolistComponent instanceof Infolists\Infolist && $infolistComponent instanceof Infolists\ComponentContainer) {
            $parentComponent = $infolistComponent->getParentComponent();

            if ($parentComponent instanceof Infolists\Components\Fieldset) {
                return AutoTranslator::translateInfolistText($parentComponent, $group, "schema.{$key}", $replace, $number, allowNull: $allowNull);
            }

            if ($parentComponent instanceof Infolists\Components\Section) {
                return AutoTranslator::translateInfolistText($parentComponent, $group, "schema.{$key}", $replace, $number, allowNull: $allowNull);
            }

            if ($parentComponent instanceof Infolists\Components\RepeatableEntry) {
                return AutoTranslator::translateInfolistText($parentComponent, $group, "schema.{$key}", $replace, $number, allowNull: $allowNull);
            }

            if ($parentComponent instanceof Infolists\Components\Tabs) {
                return AutoTranslator::translateInfolistText($parentComponent, $group, "tabs.{$key}", $replace, $number, allowNull: $allowNull);
            }

            if ($parentComponent instanceof Infolists\Components\Tabs\Tab) {
                return AutoTranslator::translateInfolistText($parentComponent, $group, "schema.{$key}", $replace, $number, allowNull: $allowNull);
            }

            return AutoTranslator::translateInfolistText($parentComponent, $group, $key, $replace, $number, allowNull: $allowNull);
        }

        if ($infolistComponent instanceof Infolists\Components\Actions\Action) {
            $component = $infolistComponent->getComponent();

            if ($component instanceof Infolists\Components\Actions || $component instanceof Infolists\Components\Actions\ActionContainer) {
                return AutoTranslator::translateInfolistText($component, $group, "actions.{$normalizedName}.{$key}", $replace, $number, allowNull: $allowNull);
            } else {
                $infolistActionTypeNamespace = null;

                $infolistActionTypes = [
                    'prefix_actions' => Infolists\Components\Contracts\HasAffixActions::class,
                    'suffix_actions' => Infolists\Components\Contracts\HasAffixActions::class,
                    'footer_actions' => Infolists\Components\Contracts\HasFooterActions::class,
                    'header_actions' => Infolists\Components\Contracts\HasHeaderActions::class,
                    'hint_actions' => Infolists\Components\Contracts\HasHintActions::class,
                ];

                foreach ($infolistActionTypes as $type => $interface) {
                    if (
                        $component instanceof $interface
                        && collect($component->{'get'.Str::studly($type)}())
                            ->map(static function (Infolists\Components\Actions\Action $action) {
                                return $action->getName();
                            })
                            ->contains($infolistComponent->getName())
                    ) {
                        $infolistActionTypeNamespace = $type;
                    }
                }

                // If no infolist action type namespace was found, default to the `actions` namespace.
                // For example, in a custom component where actions are added, this will generate a
                // key like "component_name.actions.action_name.action_key", which is a default.
                $infolistActionTypeNamespace ??= 'actions';

                return AutoTranslator::translateInfolistText($component, $group, "{$infolistActionTypeNamespace}.{$normalizedName}.{$key}", $replace, $number, allowNull: $allowNull);
            }
        }

        if ($infolistComponent instanceof Infolists\Components\Actions || $infolistComponent instanceof Infolists\Components\Actions\ActionContainer) {
            return AutoTranslator::translateInfolistText($infolistComponent->getContainer(), $group, $key, $replace, $number, allowNull: $allowNull);
        }

        if ($infolistComponent instanceof Infolists\Components\Tabs\Tab) {
            return AutoTranslator::translateInfolistText($infolistComponent->getContainer(), $group, "{$normalizedName}.{$key}", $replace, $number, allowNull: $allowNull);
        }

        if ($infolistComponent instanceof Infolists\Components\Group || $infolistComponent instanceof Infolists\Components\Grid) {
            $container = $infolistComponent->getContainer();

            if (! $container instanceof Infolists\Infolist) {
                return AutoTranslator::translateInfolistText($container, $group, $normalizedName ? "{$normalizedName}.{$key}" : $key, $replace, $number, allowNull: $allowNull);
            }
        }

        /** @var Infolists\Infolist|Infolists\ComponentContainer $container */
        $container = $infolistComponent instanceof Infolists\Infolist ? $infolistComponent : $infolistComponent->getContainer();

        if (! $container instanceof Infolists\Infolist) {
            return AutoTranslator::translateInfolistText($container, $group, $normalizedName ? "{$normalizedName}.{$key}" : $key, $replace, $number, allowNull: $allowNull);
        }

        $key = match (true) {
            $normalizedName && $group => "{$group->value}.{$normalizedName}.{$key}",
            $normalizedName && ! $group => "{$normalizedName}.{$key}",
            ! $normalizedName && $group => "{$group->value}.{$key}",
            ! $normalizedName && ! $group => $key,
        };

        if (
            ($livewire instanceof Resources\Pages\ViewRecord && ($livewire::getResourcePageName() === 'view'))
            && class_implements($resource = $livewire::getResource(), HasTranslations::class)
        ) {
            return $resource::getTranslation($key, $replace, $number, allowNull: $allowNull, pageTranslationContext: PageTranslationContext::Infolist);
        }

        if ($livewire instanceof Actions\Contracts\HasActions || $livewire instanceof Forms\Contracts\HasForms || $livewire instanceof Infolists\Contracts\HasInfolists) {
            // For infolists, there is no $operation variable present that will allow us to determine whether we are inside an action.
            // Therefore, we will use the `$container->getName()` and make an educated guess using also the `$livewire` component.
            $containerName = $container->getName();

            /** @var Actions\Contracts\HasActions|Tables\Contracts\HasTable $livewire */
            $mountedAction = match ($containerName) {
                'mountedTableActionInfolist' => $livewire->getMountedTableAction(),
                'mountedActionInfolist' => $livewire->getMountedAction(),
                'mountedFormComponentActionInfolist' => $livewire->getMountedFormComponentAction(),
                'mountedInfolistActionsInfolist' => $livewire->getMountedInfolistAction(),
                default => null,
            };

            if ($mountedAction) {
                // Redirect actions that use the Resource infolist to the Resource's translation method...
                if (
                    $livewire instanceof Resources\Pages\Page
                    && in_array($mountedAction->getName(), ['view'])
                    && $mountedAction->getModel() === $livewire::getResource()::getModel()
                    && class_implements($resource = $livewire::getResource(), HasTranslations::class)
                ) {
                    /** @var class-string<HasTranslations> $resource */
                    return $resource::getTranslation($key, $replace, $number, allowNull: $allowNull, pageTranslationContext: PageTranslationContext::Infolist);
                }

                if ($mountedAction instanceof Forms\Components\Actions\Action) {
                    return AutoTranslator::translateFormText($mountedAction, FormGroup::Fields, "infolist.{$key}", $replace, $number, allowNull: $allowNull);
                }

                if ($mountedAction instanceof Infolists\Components\Actions\Action) {
                    return AutoTranslator::translateInfolistText($mountedAction, InfolistGroup::Entries, "infolist.{$key}", $replace, $number, allowNull: $allowNull);
                }

                return AutoTranslator::translateActionText($mountedAction, ActionGroup::Infolist, $key, $replace, $number, allowNull: $allowNull);
            }
        }

        // Determine on which infolist the translation should be based on.
        $containerName = $container->getName();

        // We reached the bottom we can go (`$container instanceof Infolists\Infolist`)..
        return $livewire::getTranslation(
            key: $key,
            replace: $replace,
            number: $number,
            allowNull: $allowNull,
            pageTranslationContext: PageTranslationContext::Infolist,
            pageTranslationContextKey: $containerName !== 'infolist' ? Str::snake($containerName) : null
        );
    }

    public static function translateTableText(Tables\Table|Tables\Actions\Action|Tables\Actions\BulkAction|Tables\Columns\Column|Tables\Columns\ColumnGroup|Tables\Columns\Summarizers\Summarizer|Tables\Filters\BaseFilter|Tables\Grouping\Group $tableComponent, ?TableGroup $group, string $key, array $replace = [], Countable|float|int|null $number = null, bool $allowNull = false): ?string
    {
        $livewire = $tableComponent->getLivewire();

        if (! $livewire instanceof HasTranslations) {
            return null;
        }

        if (
            $tableComponent->isTranslationKeyAbsolute()
            && ($translationKey = $tableComponent->getTranslationKey()) !== null
        ) {
            if ($group) {
                $key = "{$group->value}.{$key}";
            }

            return static::translateAbsoluteText($translationKey, $key, $replace, $number, $allowNull);
        }

        $normalizedName = match (true) {
            $tableComponent instanceof Tables\Actions\Action => AutoTranslator::normalizeName($tableComponent->getTranslationKey() ?? $tableComponent->getName()),
            $tableComponent instanceof Tables\Actions\BulkAction => AutoTranslator::normalizeName($tableComponent->getTranslationKey() ?? $tableComponent->getName()),
            $tableComponent instanceof Tables\Columns\Column => AutoTranslator::normalizeName($tableComponent->getTranslationKey() ?? $tableComponent->getName()),
            $tableComponent instanceof Tables\Columns\ColumnGroup => AutoTranslator::normalizeName($tableComponent->getTranslationKey() ?? $tableComponent->getTranslationKey()),
            $tableComponent instanceof Tables\Columns\Summarizers\Summarizer => AutoTranslator::normalizeName($tableComponent->getTranslationKey() ?? $tableComponent->getId() ?? str($tableComponent::class)->classBasename()->kebab()),
            $tableComponent instanceof Tables\Filters\BaseFilter => AutoTranslator::normalizeName($tableComponent->getTranslationKey() ?? $tableComponent->getName()),
            $tableComponent instanceof Tables\Grouping\Group => AutoTranslator::normalizeName($tableComponent->getTranslationKey() ?? $tableComponent->getId()),
            default => null,
        };

        if ($tableComponent instanceof Tables\Columns\Summarizers\Summarizer) {
            return AutoTranslator::translateTableText($tableComponent->getColumn(), TableGroup::Columns, "summarizers.{$normalizedName}.{$key}", $replace, $number, $allowNull);
        }

        $key = match (true) {
            $normalizedName && $group => "{$group->value}.{$normalizedName}.{$key}",
            $normalizedName && ! $group => "{$normalizedName}.{$key}",
            ! $normalizedName && $group => "{$group->value}.{$key}",
            ! $normalizedName && ! $group => $key,
        };

        return $livewire::getTranslation($key, $replace, $number, allowNull: $allowNull, pageTranslationContext: PageTranslationContext::Table);
    }
}
