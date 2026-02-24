<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\Models\Service;
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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Card;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Bookings';
    protected static ?string $pluralModelLabel = 'Bookings';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Booking Information')
                    ->description('Core booking details')
                    ->schema([
                        TextInput::make('booking_code')
                            ->label('Booking Code')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan('full'),

                        Select::make('user_id')
                            ->label('Customer')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),

                        Select::make('service_id')
                            ->label('Service Type')
                            ->relationship('service', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),

                        DatePicker::make('scheduled_date')
                            ->label('Scheduled Date')
                            ->required()
                            ->minDate(now())
                            ->columnSpan(1),
                    ])->columns(3),

                Section::make('Booking Status & Pricing')
                    ->description('Manage booking progress and pricing')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending - Awaiting Confirmation',
                                'confirmed' => 'Confirmed - Ready for Service',
                                'in_progress' => 'In Progress - Currently Being Serviced',
                                'completed' => 'Completed - Service Done',
                                'cancelled' => 'Cancelled - Booking Cancelled',
                            ])
                            ->default('pending')
                            ->required()
                            ->live()
                            ->columnSpan(2),

                        TextInput::make('total_price')
                            ->label('Total Price (IDR)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('discount_amount')
                            ->label('Discount Applied (IDR)')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->columnSpan(1),

                        Select::make('voucher_id')
                            ->label('Applied Voucher')
                            ->relationship('voucher', 'code')
                            ->searchable()
                            ->columnSpan(1),

                        Placeholder::make('final_price')
                            ->label('Final Price')
                            ->content(fn (?Booking $record): string => $record ? 'Rp ' . number_format($record->total_price - $record->discount_amount, 0, '.', ',') : 'N/A')
                            ->columnSpan(1),
                    ])->columns(3),

                Section::make('Additional Information')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Service Notes')
                            ->rows(4)
                            ->columnSpan('full'),

                        Placeholder::make('created_at')
                            ->label('Created At')
                            ->content(fn (?Booking $record): string => $record?->created_at?->format('d M Y H:i') ?? 'N/A'),

                        Placeholder::make('updated_at')
                            ->label('Last Updated')
                            ->content(fn (?Booking $record): string => $record?->updated_at?->format('d M Y H:i') ?? 'N/A'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking_code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('service.name')
                    ->label('Service')
                    ->searchable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('scheduled_date')
                    ->label('Scheduled')
                    ->date()
                    ->sortable(),

                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'confirmed',
                        'secondary' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),

                TextColumn::make('total_price')
                    ->label('Total')
                    ->money('idr', divideBy: 1)
                    ->sortable(),

                TextColumn::make('discount_amount')
                    ->label('Discount')
                    ->money('idr', divideBy: 1)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),

                SelectFilter::make('service_id')
                    ->relationship('service', 'name')
                    ->label('Service Type'),

                Filter::make('scheduled_date')
                    ->form([
                        Forms\Components\DatePicker::make('scheduled_from'),
                        Forms\Components\DatePicker::make('scheduled_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['scheduled_from'] ?? null, fn (Builder $query, $date): Builder =>
                                $query->whereDate('scheduled_date', '>=', $date),
                            )
                            ->when($data['scheduled_until'] ?? null, fn (Builder $query, $date): Builder =>
                                $query->whereDate('scheduled_date', '<=', $date),
                            );
                    })
                    ->label('Scheduled Date Range'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('mark_completed')
                    ->label('Mark Completed')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Booking $record) => $record->status !== 'completed' && $record->status !== 'cancelled')
                    ->action(function (Booking $record) {
                        $record->update(['status' => 'completed']);
                        Notification::make()
                            ->title('Success')
                            ->body('Booking marked as completed. Member tier may have been updated.')
                            ->success()
                            ->send();
                    }),
                Action::make('mark_cancelled')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Booking $record) => $record->status !== 'completed' && $record->status !== 'cancelled')
                    ->action(function (Booking $record) {
                        $record->update(['status' => 'cancelled']);
                        Notification::make()
                            ->title('Success')
                            ->body('Booking cancelled.')
                            ->warning()
                            ->send();
                    }),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('scheduled_date', 'desc');
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
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
            'view' => Pages\ViewBooking::route('/{record}'),
        ];
    }
}
