<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FAQResource\Pages;
use App\Models\FAQ;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class FAQResource extends Resource
{
    protected static ?string $model = FAQ::class;
    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationLabel = 'FAQs';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('FAQ Question & Answer')
                    ->schema([
                        Select::make('category')
                            ->label('Category')
                            ->options([
                                'booking' => 'Booking & Scheduling',
                                'payment' => 'Payment & Pricing',
                                'membership' => 'Membership Tiers',
                                'voucher' => 'Vouchers & Discounts',
                                'garage' => 'Garage & Cars',
                                'other' => 'Other',
                            ])
                            ->required(),

                        TextInput::make('question')
                            ->label('Question')
                            ->required()
                            ->maxLength(500)
                            ->columnSpan('full'),

                        Textarea::make('answer')
                            ->label('Answer')
                            ->rows(5)
                            ->required()
                            ->columnSpan('full'),
                    ]),

                Section::make('Display Settings')
                    ->schema([
                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state)))
                    ->color(fn (string $state): string => match ($state) {
                        'booking' => 'info',
                        'payment' => 'success',
                        'membership' => 'warning',
                        'voucher' => 'danger',
                        'garage' => 'secondary',
                        default => 'gray',
                    }),

                TextColumn::make('question')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->alignment('center')
                    ->sortable(),

                BadgeColumn::make('is_active')
                    ->label('Status')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->colors(['success' => true, 'secondary' => false]),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'booking' => 'Booking & Scheduling',
                        'payment' => 'Payment & Pricing',
                        'membership' => 'Membership Tiers',
                        'voucher' => 'Vouchers & Discounts',
                        'garage' => 'Garage & Cars',
                        'other' => 'Other',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Status'),
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
            ->defaultSort('sort_order', 'asc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFAQS::route('/'),
            'create' => Pages\CreateFAQ::route('/create'),
            'edit' => Pages\EditFAQ::route('/{record}/edit'),
        ];
    }
}
