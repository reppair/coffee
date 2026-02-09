<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ListProductActivities extends ListActivities
{
    protected static string $resource = ProductResource::class;

    public function canRestoreActivity(): bool
    {
        return false;
    }
}
