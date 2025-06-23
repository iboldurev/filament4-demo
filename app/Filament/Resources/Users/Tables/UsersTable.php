<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums;
use Filament\Actions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                Filter::make('created_at')
                    ->columnSpanFull()
                    ->schema([
                        Schemas\Components\FusedGroup::make([
                            DatePicker::make('from')
                                ->native(false)
                                ->label('Created From')
                                ->placeholder('From'),

                            DatePicker::make('until')
                                ->native(false)
                                ->default(now())
                                ->label('Created Until')
                                ->placeholder('Until'),
                        ])
                            ->label('Created At')
                            ->columns(columns: 2),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
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
