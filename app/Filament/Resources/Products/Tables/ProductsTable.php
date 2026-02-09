<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\ProductType;
use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->sortable()
                    ->url(fn ($record) => $record->category_id ? CategoryResource::getUrl('view', ['record' => $record->category_id]) : null),
                TextColumn::make('type')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('category_id')
                    ->relationship('category', 'name'),
                SelectFilter::make('type')
                    ->options(ProductType::class),
                TernaryFilter::make('is_active'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
            ]);
    }
}
