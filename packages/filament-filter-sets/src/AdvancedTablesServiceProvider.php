<?php

namespace Archilex\AdvancedTables;

use Archilex\AdvancedTables\Commands\AddTenancyCommand;
use Archilex\AdvancedTables\Support\Config;
use Filament\Tables\Columns\Column;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AdvancedTablesServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('advanced-tables')
            ->hasCommands([
                AddTenancyCommand::class,
            ])
            ->hasConfigFile('advanced-tables')
            ->hasViews()
            ->hasTranslations()
            ->hasMigrations([
                'create_filament_filter_sets_table',
                'create_filament_filter_set_user_table',
                'add_icon_and_color_columns_to_filter_sets_table',
                'add_is_visible_column_to_filter_set_users_table',
                'create_filament_filter_sets_managed_preset_views_table',
                'add_status_column_to_filter_sets_table',
                'change_filter_json_column_type_to_text_type',
                'add_tenant_id_to_filter_sets_table',
                'add_tenant_id_to_managed_preset_views_table',
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->booted(function () {
            if (Config::tableHasLoadingOverlay()) {
                Column::configureUsing(function (Column $column) {
                    return $column->extraCellAttributes([
                        'wire:loading.class' => 'advanced-tables-table-loading-overlay',
                        'wire:target' => 'activeTab, applyTableFilters, gotoPage, loadPresetView, loadUserView, nextPage, previousPage,removeTableFilter, removeTableFilters, resetTableFiltersForm, resetTableToDefault, sortTable, tableColumnSearches, tableFilters, tableGrouping, tableGroupingDirection, tableRecordsPerPage, tableSearch, toggledTableColumns, toggleOnAllTableColumns',
                    ]);
                });
            }
        });
    }
}
