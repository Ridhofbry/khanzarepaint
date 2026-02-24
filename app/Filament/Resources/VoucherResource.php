<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VoucherResource\Pages;
use App\Models\Voucher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationLabel = 'Vouchers & Discounts';
    protected static ?string $pluralModelLabel = 'Vouchers';
    protected static ?string $modelLabel = 'Voucher';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Voucher Code & Type')
                    ->description('Create unique discount codes')
                    ->schema([
                        TextInput::make('code')
                            ->label('Voucher Code')
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->maxLength(50)
                            ->hint('Use uppercase letters and numbers (e.g., REPAINT30)')
                            ->columnSpan(1),

                        Select::make('discount_type')
                            ->options([
                                'fixed' => 'Fixed Amount (IDR)',
                                'percentage' => 'Percentage (%)',
                            ])
                            ->default('percentage')
                            ->required()
                            ->live()
                            ->columnSpan(1),

                        TextInput::make('discount_amount')
                            ->label(fn (Forms\Get $get) => $get('discount_type') === 'percentage' ? 'Discount %' : 'Discount Amount (IDR)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(fn (Forms\Get $get) => $get('discount_type') === 'percentage' ? 100 : 999999999)
                            ->columnSpan(1),
                    ])->columns(3),

                Section::make('Validity & Usage Limits')
                    ->description('Set when and how often the voucher can be used')
                    ->schema([
                        DateTimePicker::make('starts_at')
                            ->label('Valid From')
                            ->required()
                            ->columnSpan(1),

                        DateTimePicker::make('expires_at')
                            ->label('Expires At')
                            ->required()
                            ->minDate(fn (Forms\Get $get) => $get('starts_at') ?? now())
                            ->columnSpan(1),

                        TextInput::make('max_uses')
                            ->label('Maximum Uses (0 = Unlimited)')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->columnSpan(1),

                        Placeholder::make('current_uses')
                            ->label('Currently Used')
                            ->content(fn (?Voucher $record): string => $record?->current_uses ?? '0'),
                    ])->columns(3),

                Section::make('Conditions')
                    ->description('Set minimum purchase and applicable services')
                    ->schema([
                        TextInput::make('minimum_purchase')
                            ->label('Minimum Purchase Amount (IDR)')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->prefix('Rp')
                            ->columnSpan(1),

                        Select::make('applicable_to')
                            ->label('Applicable To')
                            ->options([
                                'all' => 'All Services',
                                'repaint' => 'Repaint Only',
                                'general' => 'General Services Only',
                            ])
                            ->default('all')
                            ->columnSpan(1),

                        Placeholder::make('status_placeholder')
                            ->label('Status')
                            ->content(fn (?Voucher $record): string => self::getVoucherStatus($record))
                            ->columnSpan(1),
                    ])->columns(3),

                Section::make('Description')
                    ->schema([
                        Textarea::make('description')
                            ->label('Voucher Description (for admin reference)')
                            ->rows(3)
                            ->columnSpan('full'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('discount_display')
                    ->label('Discount')
                    ->formatStateUsing(fn (Voucher $record): string =>
                        $record->discount_type === 'percentage'
                            ? $record->discount_amount . '%'
                            : 'Rp ' . number_format($record->discount_amount, 0, '.', ',')
                    )
                    ->sortable(),

                TextColumn::make('applicable_to')
                    ->label('Applies To')
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state)))
                    ->badge(),

                TextColumn::make('usage')
                    ->label('Usage')
                    ->formatStateUsing(fn (Voucher $record): string =>
                        $record->max_uses === 0
                            ? "{$record->current_uses} uses (Unlimited)"
                            : "{$record->current_uses}/{$record->max_uses} uses"
                    ),

                TextColumn::make('minimum_purchase')
                    ->label('Min Purchase')
                    ->money('idr', divideBy: 1)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime()
                    ->sortable()
                    ->color(fn (?Voucher $record): string =>
                        $record && $record->expires_at->isPast() ? 'danger' : 'success'
                    ),

                BadgeColumn::make('status_badge')
                    ->label('Status')
                    ->getStateUsing(fn (Voucher $record): string => self::getVoucherStatusValue($record))
                    ->colors([
                        'success' => 'active',
                        'warning' => 'expired',
                        'secondary' => 'limit_reached',
                    ]),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('applicable_to')
                    ->options([
                        'all' => 'All Services',
                        'repaint' => 'Repaint Only',
                        'general' => 'General Services Only',
                    ]),

                SelectFilter::make('discount_type')
                    ->options([
                        'fixed' => 'Fixed Amount',
                        'percentage' => 'Percentage',
                    ]),

                Filter::make('active')
                    ->label('Active Vouchers')
                    ->query(fn (Builder $query): Builder =>
                        $query
                            ->where('starts_at', '<=', now())
                            ->where('expires_at', '>=', now())
                            ->where(fn (Builder $q) => $q
                                ->where('max_uses', 0)
                                ->orWhereColumn('current_uses', '<', 'max_uses')
                            )
                    )
                    ->toggle(),

                Filter::make('expired')
                    ->label('Expired Vouchers')
                    ->query(fn (Builder $query): Builder =>
                        $query->where('expires_at', '<', now())
                    )
                    ->toggle(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (Voucher $record) {
                        $duplicate = $record->replicate();
                        $duplicate->code = $record->code . '-COPY-' . now()->format('YmdHis');
                        $duplicate->current_uses = 0;
                        $duplicate->save();

                        Notification::make()
                            ->title('Success')
                            ->body('Voucher duplicated with code: ' . $duplicate->code)
                            ->success()
                            ->send();
                    }),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('expires_at', 'asc');
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
            'index' => Pages\ListVouchers::route('/'),
            'create' => Pages\CreateVoucher::route('/create'),
            'edit' => Pages\EditVoucher::route('/{record}/edit'),
            'view' => Pages\ViewVoucher::route('/{record}'),
        ];
    }

    private static function getVoucherStatus(?Voucher $record): string
    {
        if (!$record) {
            return 'N/A';
        }

        if ($record->expires_at->isPast()) {
            return 'Expired';
        }

        if ($record->max_uses > 0 && $record->current_uses >= $record->max_uses) {
            return 'Limit Reached';
        }

        return 'Active';
    }

    private static function getVoucherStatusValue(?Voucher $record): string
    {
        if (!$record) {
            return 'inactive';
        }

        if ($record->expires_at->isPast()) {
            return 'expired';
        }

        if ($record->max_uses > 0 && $record->current_uses >= $record->max_uses) {
            return 'limit_reached';
        }

        return 'active';
    }
}
