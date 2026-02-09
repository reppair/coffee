<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductType;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make([
                    Toggle::make('is_active')
                        ->default(true),
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->hint('Changing the name updates the URL slug, which may affect SEO'),
                    Textarea::make('description'),
                    Select::make('category_id')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload(),
                    Select::make('type')
                        ->options(ProductType::class)
                        ->required(),
                    TextInput::make('sku')
                        ->maxLength(255),
                ]),

                FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->directory('products')
                    ->visibility('public'),
            ]);
    }
}
