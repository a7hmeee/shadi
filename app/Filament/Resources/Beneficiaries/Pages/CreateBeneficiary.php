<?php

namespace App\Filament\Resources\Beneficiaries\Pages;

use App\Domains\Beneficiaries\Actions\CreateBeneficiaryAction;
use App\Domains\Beneficiaries\DTOs\BeneficiaryData;
use App\Domains\Beneficiaries\Models\Beneficiary;
use App\Filament\Resources\Beneficiaries\BeneficiaryResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBeneficiary extends CreateRecord
{
    protected static string $resource = BeneficiaryResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $dto = BeneficiaryData::fromArray($data);

        return app(CreateBeneficiaryAction::class)->execute($dto);
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('تم إنشاء المستفيد بنجاح')
            ->success();
    }
}
