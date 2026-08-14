<?php

namespace App\Filament\Resources\Beneficiaries\Pages;

use App\Domains\Beneficiaries\Actions\UpdateBeneficiaryAction;
use App\Domains\Beneficiaries\DTOs\BeneficiaryData;
use App\Domains\Beneficiaries\Models\Beneficiary;
use App\Filament\Resources\Beneficiaries\BeneficiaryResource;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditBeneficiary extends EditRecord
{
    protected static string $resource = BeneficiaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return $this->getResourceUrl('view', ['record' => $this->getRecord()]);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // user_id is disabled in the form, preserve the original
        $data['user_id'] = $this->record->user_id;

        $dto = BeneficiaryData::fromArray($data);

        return app(UpdateBeneficiaryAction::class)->execute($this->record, $dto);
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('تم تحديث بيانات المستفيد بنجاح')
            ->success();
    }
}
