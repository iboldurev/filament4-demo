<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums;
use Filament\Actions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Users')
            ->description('List of registered users')
            ->columns([
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filtersFormWidth(Width::TwoExtraLarge)
            ->filtersTriggerAction(
                fn (Actions\Action $action) => $action
                    ->slideOver()
                    ->stickyModalHeader()
                    ->stickyModalFooter()
                    ->closeModalByEscaping(false)
                    ->closeModalByClickingAway(false)
                    ->modalFooterActionsAlignment(Alignment::Start)
            )
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(Enums\UserStatus::class)
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make()
                    ->slideOver(),
                EditAction::make()
                    ->slideOver(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->slideOver()
                    ->createAnother(false),
            ]);
    }
}
