<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ListCategoryActivities extends ListActivities
{
    protected static string $resource = CategoryResource::class;

    public function canRestoreActivity(): bool
    {
        return false;
    }
}
