<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewCategory extends ViewRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('activities')
                ->label('View Activities')
                ->icon(Heroicon::OutlinedClock)
                ->url(fn (): string => CategoryResource::getUrl('activities', ['record' => $this->record])),
            EditAction::make(),
            DeleteAction::make()
                ->authorizationTooltip(),
        ];
    }
}
