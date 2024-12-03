<?php

namespace App\Filament\Resources;

use Forms\Set;
use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Enums\ProductTypeEnum;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProductResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProductResource\RelationManagers;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationLabel = 'Products'; //Navbar Label Name

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Shop'; //Navbar Group Name

    protected static ?int $navigationSort = 0; //Sorting in navbar

    protected static ?string $recordTitleAttribute = 'name';

    protected static int $globalSearchResultLimit = 20;

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug', 'description'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Brand' => $record->brand->name,
            'Description' => $record->description,
            'Published' => $record->published_at,
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['Brand']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->afterStateUpdated(function (string $operation, string $state, Forms\Set $set) {
                                $set('slug', Str::slug($state));
                                })
                                ->live(onBlur: true)
                                ->required()
                                ->maxLength(255)
                                ->helperText('Enter specific name of product.'),
                            Forms\Components\TextInput::make('slug')
                                ->readOnly()
                                ->unique(ignorable: fn($record) => $record)
                                ->required()
                                ->maxLength(255)
                                ->helperText('Auto-generate.'),
                            Forms\Components\Markdowneditor::make('description')
                                ->columnSpan('full')

                            ])->columns(2),

                        Forms\Components\Section::make('Pricing & Inventory')
                        ->schema([
                            Forms\Components\TextInput::make('sku')
                                ->label('SKU (Stock Keeping Unit)')
                                ->unique()
                                ->required(),
                            Forms\Components\TextInput::make('price')
                                ->numeric()
                                ->prefix('RM ')
                                // ->rules(rules:'regex:/^\d{1,6}(\.\d{0,2)?$/') // REGEX TAK FAHAM
                                ->minValue('0')
                                ->required(),
                            Forms\Components\TextInput::make('quantity')
                                ->numeric()
                                ->minValue('0')
                                ->maxValue('100')
                                ->required(),
                            Forms\Components\Select::make('type')
                                ->options([
                                    'downloadable' => ProductTypeEnum::DOWNLOADABLE->value,
                                    'deliverable' => ProductTypeEnum::DELIVERABLE->value,
                                ])->required()

                            ])->columns(2),
                        ]),


                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Status')
                        ->schema([
                            Forms\Components\Toggle::make('is_visible')
                                ->label('Visibility')
                                ->helperText('Enable or disable product visibility.')
                                ->default(true),
                            Forms\Components\Toggle::make('is_featured')
                                ->label('Featured')
                                ->helperText('Enable or disable product featured status.'),
                            Forms\Components\DatePicker::make('published_at')
                                ->label('Availability')
                                ->default(now())

                        ]),

                Forms\Components\Section::make('Image')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                        ->directory('form-attachments')
                        ->preserveFilenames()
                        ->image()
                        ->imageEditor()
                        ->hiddenLabel(),
                    ])->collapsible(),

                Forms\Components\Section::make('Associations')
                    ->schema([
                        Forms\Components\Select::make('brand_id')
                        ->label('brand name')
                        ->relationship('brand','name')
                    ])->collapsible(),
            ]),


                // Forms\Components\Select::make('brand_id')
                //     ->relationship('brand', 'name')
                //     ->required(),
                // Forms\Components\TextInput::make('sku')
                //     ->label('SKU')
                //     ->required()
                //     ->maxLength(255),
                // Forms\Components\FileUpload::make('image')
                //     ->image()
                //     ->required(),
                // Forms\Components\Textarea::make('description')
                //     ->columnSpanFull(),
                // Forms\Components\TextInput::make('quantity')
                //     ->required()
                //     ->numeric()
                //     ->minValue('0'),
                // Forms\Components\TextInput::make('price')
                //     ->required()
                //     ->numeric()
                //     ->prefix('RM ')
                //     ->minValue('0'),
                // Forms\Components\Toggle::make('is_visible')
                //     ->required(),
                // Forms\Components\Toggle::make('is_featured')
                //     ->required(),
                // Forms\Components\TextInput::make('type')
                //     ->required(),
                // Forms\Components\DatePicker::make('published_at')
                //     ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('brand.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label('Visibility')
                    ->boolean(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('RM ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('published_at')
                    ->date('d-m-Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label('Visibility')
                    ->boolean()
                    ->trueLabel('Only Visible Products')
                    ->falseLabel('Only Hidden Products')
                    ->native(false),

                Tables\Filters\SelectFilter::make('brand')
                    ->relationship('brand','name'),

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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
