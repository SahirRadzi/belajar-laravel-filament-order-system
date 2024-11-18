<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\OrderStatusEnum;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\OrderResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OrderResource\RelationManagers;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Orders';

    protected static ?string $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Order Details')
                        ->schema([
                            Forms\Components\TextInput::make('number')
                                ->default('OR-' . random_int(100000,9999999))
                                ->readOnly(),
                            Forms\Components\Select::make('customer_id')
                                ->relationship('customer', 'name')
                                ->searchable()
                                ->preload(),
                                // ->required(),
                            Forms\Components\Select::make('status')
                                ->options([
                                    'pending' => OrderStatusEnum::PENDING->value,
                                    'processing' => OrderStatusEnum::PROCESSING->value,
                                    'completed' => OrderStatusEnum::COMPLETED->value,
                                    'declined' => OrderStatusEnum::DECLINED->value,
                                ])->columnSpanFull(),
                                // ->required(),

                            Forms\Components\Markdowneditor::make('notes')
                                // ->required()
                                ->columnSpanFull(),
                        ])->columns(2),
                    Forms\Components\Wizard\Step::make('Order Items')
                        ->schema([
                           Forms\Components\Repeater::make('Items')
                           ->relationship()
                           ->schema([
                            Forms\Components\Select::make('product_id')
                            ->label('Product')
                            ->options(Product::query()->pluck('name','id')),

                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->default(1)
                            ->required(),

                        Forms\Components\TextInput::make('unit_price')
                            ->label('Unit Price')
                            ->readOnly()
                            ->numeric()
                            ->required(),

                           ])->columns(3)
                        ])
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->numeric()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('RM ')
                    ]),
                Tables\Columns\TextColumn::make('shipping_price')
                    ->money('RM ')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->color('success'),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
