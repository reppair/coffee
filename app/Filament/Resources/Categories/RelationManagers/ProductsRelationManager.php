<?php

namespace App\Filament\Resources\Categories\RelationManagers;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

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
            ->filters([])
            ->headerActions([])
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record) => ProductResource::getUrl('view', ['record' => $record->id])),
            ])
            ->toolbarActions([]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
