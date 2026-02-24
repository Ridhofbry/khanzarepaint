<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Customers';
    protected static ?string $pluralModelLabel = 'Customers';
    protected static ?string $modelLabel = 'Customer';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Personal Information')
                    ->description('Customer basic details')
                    ->schema([
                        TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),
                        TextInput::make('address')
                            ->maxLength(500),
                    ])->columns(2),

                Section::make('Membership Information')
                    ->description('Track customer membership tier and service history')
                    ->schema([
                        Select::make('membership_tier')
                            ->options([
                                'none' => 'None',
                                'bronze' => 'Bronze (2-3 services)',
                                'silver' => 'Silver (4-6 services)',
                                'gold' => 'Gold (7+ services)',
                            ])
                            ->default('none')
                            ->required(),
                        TextInput::make('service_count')
                            ->label('Total Services Completed')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(),
                        TextInput::make('total_spent')
                            ->label('Total Amount Spent (IDR)')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(),
                        Placeholder::make('created_at')
                            ->label('Member Since')
                            ->content(fn (?User $record): string => $record?->created_at?->format('d M Y') ?? 'N/A'),
                    ])->columns(2),

                Section::make('Profile Image')
                    ->schema([
                        FileUpload::make('profile_image')
                            ->label('Profile Picture')
                            ->image()
                            ->maxSize(5120)
                            ->directory('profiles'),
                    ]),

                Section::make('Account Status')
                    ->schema([
                        Select::make('role')
                            ->options([
                                'customer' => 'Customer',
                                'garage_owner' => 'Garage Owner',
                                'admin' => 'Admin',
                            ])
                            ->default('customer')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('profile_image')
                    ->label('Picture')
                    ->circular()
                    ->defaultImageUrl(url('/images/avatar-placeholder.png')),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->icon('heroicon-m-envelope'),

                TextColumn::make('phone')
                    ->searchable()
                    ->copyable(),

                BadgeColumn::make('membership_tier')
                    ->label('Tier')
                    ->colors([
                        'secondary' => 'none',
                        'warning' => 'bronze',
                        'info' => 'silver',
                        'success' => 'gold',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('service_count')
                    ->label('Services')
                    ->numeric()
                    ->sortable()
                    ->alignment('center'),

                TextColumn::make('total_spent')
                    ->label('Total Spent')
                    ->money('idr', divideBy: 1)
                    ->sortable(),

                TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'customer' => 'gray',
                        'garage_owner' => 'info',
                        'admin' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),

                TextColumn::make('created_at')
                    ->label('Joined')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('membership_tier')
                    ->options([
                        'none' => 'None',
                        'bronze' => 'Bronze',
                        'silver' => 'Silver',
                        'gold' => 'Gold',
                    ]),
                SelectFilter::make('role')
                    ->options([
                        'customer' => 'Customer',
                        'garage_owner' => 'Garage Owner',
                        'admin' => 'Admin',
                    ]),
                TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->when(auth()->user()?->isStaff() ?? false, function ($query) {
                return $query->where('role', '=', 'customer');
            });
    }
}
