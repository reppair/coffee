<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('activities')
                ->label('View Activities')
                ->icon(Heroicon::OutlinedClock)
                ->url(fn (): string => ProductResource::getUrl('activities', ['record' => $this->record])),
            EditAction::make(),
            DeleteAction::make()
                ->authorizationTooltip(),
            ForceDeleteAction::make()
                ->authorizationTooltip(),
            RestoreAction::make(),
        ];
    }
}
