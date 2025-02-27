<?php

namespace App\Filament\Resources;

use Forms\Set;
use Filament\Forms;
use Filament\Tables;
use App\Models\Brand;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\BrandResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BrandResource\RelationManagers;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationLabel = 'Brands'; //Navbar Label Name

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Shop'; //Navbar Group Name

    protected static ?int $navigationSort = 1; // Sorting in Navbar

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
               Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make([
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
                Forms\Components\TextInput::make('url')
                    ->label('Website Url')
                    ->required()
                    ->unique()
                    ->columnSpan('full'),
                Forms\Components\Markdowneditor::make('description')
                    ->columnSpan('full'),

                    ])->columns(2),
                ]),

               Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Status')
                        ->schema([
                            Forms\Components\Toggle::make('is_visible')
                                ->label('Visibility')
                                ->helperText('Enable or disable brand visibility.')
                                ->default(true),
                        ]),
               Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Color')
                        ->schema([
                            Forms\Components\ColorPicker::make('primary_hex')
                                ->label('Primary Color'),
                        ])
                    ])

                ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('url')
                    ->searchable(),
                Tables\Columns\ColorColumn::make('primary_hex')
                    ->label('Primary Color'),
                Tables\Columns\IconColumn::make('is_visible')
                    ->boolean()
                    ->label('Visibility'),
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
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'view' => Pages\ViewBrand::route('/{record}'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
