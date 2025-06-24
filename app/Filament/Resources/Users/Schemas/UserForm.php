<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('status')
                    ->label('Status')
                    ->native(false)
                    ->options(Enums\UserStatus::class)
                    ->required(),

                TextInput::make('name')
                    ->required(),

                TextInput::make('email')
                    ->email()
                    ->required(),

                DateTimePicker::make('email_verified_at')
                    ->native(false)
                    ->time(false),

                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                    ->default(null),

                Repeater::make('contacts')
                    ->schema([
                        Select::make('type')
                            ->label('Type')
                            ->native(false)
                            ->options(Enums\ContactType::class)
                            ->required(),

                        TextInput::make('value')
                            ->label('Value')
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
