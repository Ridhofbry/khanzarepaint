<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarGarageResource\Pages;
use App\Models\Car;
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
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TagsInput;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class CarGarageResource extends Resource
{
    protected static ?string $model = Car::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Garage Inventory';
    protected static ?string $pluralModelLabel = 'Cars';
    protected static ?string $modelLabel = 'Car';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Vehicle Information')
                    ->description('Basic car details')
                    ->schema([
                        TextInput::make('brand')
                            ->label('Brand/Make')
                            ->required()
                            ->maxLength(100)
                            ->columnSpan(1),

                        TextInput::make('model')
                            ->label('Model')
                            ->required()
                            ->maxLength(100)
                            ->columnSpan(1),

                        TextInput::make('year')
                            ->label('Year')
                            ->numeric()
                            ->required()
                            ->minValue(1990)
                            ->maxValue(date('Y') + 1)
                            ->columnSpan(1),

                        Select::make('fuel_type')
                            ->options([
                                'petrol' => 'Petrol',
                                'diesel' => 'Diesel',
                                'hybrid' => 'Hybrid',
                                'electric' => 'Electric',
                            ])
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('mileage')
                            ->label('Mileage (KM)')
                            ->numeric()
                            ->suffix('KM')
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('color')
                            ->maxLength(50)
                            ->columnSpan(1),
                    ])->columns(3),

                Section::make('Pricing & Inventory')
                    ->description('Price and availability settings')
                    ->schema([
                        TextInput::make('price')
                            ->label('Price (IDR)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->columnSpan(1),

                        Select::make('status')
                            ->options([
                                'available' => 'Available for Sale',
                                'sold' => 'Sold',
                                'pending' => 'Pending Sale',
                                'maintenance' => 'Under Maintenance',
                            ])
                            ->default('available')
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('views_count')
                            ->label('View Count')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),
                    ])->columns(3),

                Section::make('Seller Information')
                    ->description('Who is selling this car')
                    ->schema([
                        Select::make('seller_id')
                            ->label('Seller/Owner')
                            ->relationship('seller', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(2),

                        Placeholder::make('seller_contact')
                            ->label('Contact')
                            ->content(fn (?Car $record): string => $record?->seller?->phone ?? 'N/A')
                            ->columnSpan(1),
                    ])->columns(3),

                Section::make('Vehicle Images')
                    ->description('Upload car images to Cloudinary')
                    ->schema([
                        FileUpload::make('images')
                            ->label('Car Photos')
                            ->image()
                            ->multiple()
                            ->maxFiles(5)
                            ->maxSize(10240)
                            ->directory('garage/cars')
                            ->disk('cloudinary')
                            ->helperText('Upload up to 5 images. Images will be optimized automatically.')
                            ->columnSpan('full'),
                    ]),

                Section::make('Additional Details')
                    ->schema([
                        Textarea::make('features')
                            ->label('Vehicle Features & Specifications')
                            ->rows(4)
                            ->columnSpan('full')
                            ->helperText('Describe features like AC, power steering, airbags, etc.'),

                        Placeholder::make('created_at')
                            ->label('Listed Since')
                            ->content(fn (?Car $record): string => $record?->created_at?->format('d M Y') ?? 'N/A'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('images')
                    ->label('Image')
                    ->circular(),

                TextColumn::make('brand')
                    ->label('Vehicle')
                    ->formatStateUsing(fn (Car $record): string => "{$record->brand} {$record->model} ({$record->year})")
                    ->searchable(['brand', 'model'])
                    ->sortable(),

                TextColumn::make('color')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('fuel_type')
                    ->label('Fuel')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'petrol' => 'info',
                        'diesel' => 'warning',
                        'hybrid' => 'success',
                        'electric' => 'danger',
                    }),

                TextColumn::make('mileage')
                    ->label('Mileage')
                    ->formatStateUsing(fn (int $state): string => number_format($state) . ' KM'),

                TextColumn::make('price')
                    ->label('Price')
                    ->money('idr', divideBy: 1)
                    ->sortable(),

                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'available',
                        'danger' => 'sold',
                        'warning' => 'pending',
                        'secondary' => 'maintenance',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),

                TextColumn::make('seller.name')
                    ->label('Seller')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('views_count')
                    ->label('Views')
                    ->alignment('center'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'available' => 'Available',
                        'sold' => 'Sold',
                        'pending' => 'Pending',
                        'maintenance' => 'Maintenance',
                    ]),

                SelectFilter::make('fuel_type')
                    ->options([
                        'petrol' => 'Petrol',
                        'diesel' => 'Diesel',
                        'hybrid' => 'Hybrid',
                        'electric' => 'Electric',
                    ]),
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
            'index' => Pages\ListCarGarages::route('/'),
            'create' => Pages\CreateCarGarage::route('/create'),
            'edit' => Pages\EditCarGarage::route('/{record}/edit'),
            'view' => Pages\ViewCarGarage::route('/{record}'),
        ];
    }
}
