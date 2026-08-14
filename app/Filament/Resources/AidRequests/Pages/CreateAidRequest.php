<?php

namespace App\Filament\Resources\AidRequests\Pages;

use App\Domains\AidRequests\Actions\ApproveAidRequestAction;
use App\Domains\AidRequests\Actions\CreateAidRequestAction;
use App\Domains\AidRequests\Actions\RejectAidRequestAction;
use App\Domains\AidRequests\Actions\RequestMoreInformationAction;
use App\Domains\AidRequests\Actions\StartAidRequestReviewAction;
use App\Domains\AidRequests\DTOs\AidRequestData;
use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Models\AidRequest;
use App\Filament\Resources\AidRequests\AidRequestResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAidRequest extends CreateRecord
{
    protected static string $resource = AidRequestResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $dto = AidRequestData::fromArray($data);

        return app(CreateAidRequestAction::class)->execute($dto);
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('تم إنشاء طلب المساعدة بنجاح')
            ->success();
    }
}
