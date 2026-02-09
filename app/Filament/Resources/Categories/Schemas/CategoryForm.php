<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
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
                ]),
            ]);
    }
}
