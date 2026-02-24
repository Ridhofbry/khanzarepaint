<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestimonialResource\Pages;
use App\Models\Testimonial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\SelectFilter;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationLabel = 'Testimonials';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Testimonial Information')
                    ->schema([
                        Select::make('user_id')
                            ->label('Customer')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('booking_id')
                            ->label('Related Booking')
                            ->relationship('booking', 'booking_code')
                            ->searchable()
                            ->preload(),

                        TextInput::make('title')
                            ->label('Review Title')
                            ->required()
                            ->maxLength(255),

                        Select::make('rating')
                            ->label('Rating')
                            ->options([
                                1 => '1 ⭐ - Poor',
                                2 => '2 ⭐⭐ - Fair',
                                3 => '3 ⭐⭐⭐ - Good',
                                4 => '4 ⭐⭐⭐⭐ - Very Good',
                                5 => '5 ⭐⭐⭐⭐⭐ - Excellent',
                            ])
                            ->required(),
                    ])->columns(2),

                Section::make('Review Content')
                    ->schema([
                        Textarea::make('content')
                            ->label('Review Text')
                            ->rows(5)
                            ->required()
                            ->columnSpan('full'),
                    ]),

                Section::make('Publication Settings')
                    ->schema([
                        Toggle::make('is_approved')
                            ->label('Approved')
                            ->default(false),

                        Toggle::make('is_featured')
                            ->label('Featured on Homepage')
                            ->default(false),

                        Placeholder::make('created_at')
                            ->label('Submitted At')
                            ->content(fn (?Testimonial $record): string => $record?->created_at?->format('d M Y H:i') ?? 'N/A'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('rating')
                    ->formatStateUsing(fn (int $state): string => str_repeat('⭐', $state))
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        1, 2 => 'danger',
                        3 => 'warning',
                        4, 5 => 'success',
                    })
                    ->alignment('center'),

                BadgeColumn::make('is_approved')
                    ->label('Status')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Approved' : 'Pending')
                    ->colors(['success' => true, 'warning' => false]),

                BadgeColumn::make('is_featured')
                    ->label('Featured')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No')
                    ->colors(['success' => true, 'secondary' => false]),

                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_approved')
                    ->label('Approval Status'),

                TernaryFilter::make('is_featured')
                    ->label('Featured'),

                SelectFilter::make('rating')
                    ->options([
                        5 => '5 Stars',
                        4 => '4 Stars',
                        3 => '3 Stars',
                        2 => '2 Stars',
                        1 => '1 Star',
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTestimonials::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit' => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }
}
