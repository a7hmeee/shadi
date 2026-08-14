<?php

namespace App\Filament\Resources\AidRequests\Pages;

use App\Filament\Resources\AidRequests\AidRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAidRequest extends ViewRecord
{
    protected static string $resource = AidRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
