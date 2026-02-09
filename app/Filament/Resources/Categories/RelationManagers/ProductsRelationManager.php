<?php

namespace App\Filament\Resources\Categories\RelationManagers;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->defaultSort('category_sort_order')
            ->reorderable('category_sort_order')
            ->recordActions([
                ViewAction::make()
                    ->url(fn (Product $record): string => ProductResource::getUrl('view', ['record' => $record->id])),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
