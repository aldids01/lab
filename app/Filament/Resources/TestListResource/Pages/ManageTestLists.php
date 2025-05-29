<?php

namespace App\Filament\Resources\TestListResource\Pages;

use App\Filament\Resources\TestListResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\MaxWidth;

class ManageTestLists extends ManageRecords
{
    protected static string $resource = TestListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->slideOver()
                ->modalWidth(MaxWidth::FitContent),
        ];
    }
}
