<?php

namespace App\Filament\Resources\AidRequests\Pages;

use App\Domains\AidRequests\Actions\ApproveAidRequestAction;
use App\Domains\AidRequests\Actions\RejectAidRequestAction;
use App\Domains\AidRequests\Actions\RequestMoreInformationAction;
use App\Domains\AidRequests\Actions\StartAidRequestReviewAction;
use App\Domains\AidRequests\Actions\UpdateAidRequestAction;
use App\Domains\AidRequests\DTOs\AidRequestData;
use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Models\AidRequest;
use App\Filament\Resources\AidRequests\AidRequestResource;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditAidRequest extends EditRecord
{
    protected static string $resource = AidRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),

            // Start Review — allowed from submitted / needs_information
            Action::make('startReview')
                ->label('بدء المراجعة')
                ->icon('heroicon-o-magnifying-glass')
                ->visible(fn () => in_array($this->getRecord()->status, [
                    AidRequestStatus::SUBMITTED,
                    AidRequestStatus::NEEDS_INFORMATION,
                ], true))
                ->authorize(fn () => auth()->user()->can('approve', $this->getRecord()))
                ->action(function () {
                    $record = $this->getRecord();
                    app(StartAidRequestReviewAction::class)->execute($record, auth()->user());
                    Notification::make()->title('تم بدء مراجعة الطلب')->success()->send();
                    $this->redirect($this->getResourceUrl('view', ['record' => $record]));
                }),

            // Request More Information — allowed from submitted / under_review
            Action::make('requestInfo')
                ->label('طلب معلومات إضافية')
                ->icon('heroicon-o-question-mark-circle')
                ->visible(fn () => in_array($this->getRecord()->status, [
                    AidRequestStatus::SUBMITTED,
                    AidRequestStatus::UNDER_REVIEW,
                ], true))
                ->authorize(fn () => auth()->user()->can('review', $this->getRecord()))
                ->form([
                    \Filament\Forms\Components\Textarea::make('reviewer_notes')
                        ->label('ملاحظات المراجع'),
                ])
                ->action(function (array $data) {
                    $record = $this->getRecord();
                    app(RequestMoreInformationAction::class)->execute($record, auth()->user(), $data['reviewer_notes'] ?? null);
                    Notification::make()->title('تم طلب معلومات إضافية من المستفيد')->success()->send();
                    $this->redirect($this->getResourceUrl('view', ['record' => $record]));
                }),

            // Approve — allowed from submitted / under_review / needs_information
            Action::make('approve')
                ->label('الموافقة')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => in_array($this->getRecord()->status, [
                    AidRequestStatus::SUBMITTED,
                    AidRequestStatus::UNDER_REVIEW,
                    AidRequestStatus::NEEDS_INFORMATION,
                ], true))
                ->authorize(fn () => auth()->user()->can('approve', $this->getRecord()))
                ->form([
                    \Filament\Forms\Components\TextInput::make('approved_amount')
                        ->label('المبلغ الموافق عليه')
                        ->numeric()
                        ->minValue(0),
                    \Filament\Forms\Components\Textarea::make('reviewer_notes')
                        ->label('ملاحظات المراجع'),
                ])
                ->action(function (array $data) {
                    $record = $this->getRecord();
                    app(ApproveAidRequestAction::class)->execute(
                        $record,
                        auth()->user(),
                        isset($data['approved_amount']) ? (float) $data['approved_amount'] : null,
                        $data['reviewer_notes'] ?? null,
                    );
                    Notification::make()->title('تمت الموافقة على الطلب')->success()->send();
                    $this->redirect($this->getResourceUrl('view', ['record' => $record]));
                }),

            // Reject — allowed from submitted / under_review / needs_information
            Action::make('reject')
                ->label('رفض الطلب')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => in_array($this->getRecord()->status, [
                    AidRequestStatus::SUBMITTED,
                    AidRequestStatus::UNDER_REVIEW,
                    AidRequestStatus::NEEDS_INFORMATION,
                ], true))
                ->authorize(fn () => auth()->user()->can('reject', $this->getRecord()))
                ->form([
                    \Filament\Forms\Components\Textarea::make('rejection_reason')
                        ->label('سبب الرفض')
                        ->required()
                        ->default('لا يستوفي الشروط المطلوبة'),
                    \Filament\Forms\Components\Textarea::make('reviewer_notes')
                        ->label('ملاحظات المراجع'),
                ])
                ->action(function (array $data) {
                    $record = $this->getRecord();
                    app(RejectAidRequestAction::class)->execute(
                        $record,
                        auth()->user(),
                        $data['rejection_reason'],
                        $data['reviewer_notes'] ?? null,
                    );
                    Notification::make()->title('تم رفض الطلب')->success()->send();
                    $this->redirect($this->getResourceUrl('view', ['record' => $record]));
                }),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return $this->getResourceUrl('view', ['record' => $this->getRecord()]);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // beneficiary_id is disabled in form; preserve original
        $data['beneficiary_id'] = $this->record->beneficiary_id;

        $dto = AidRequestData::fromArray($data);

        return app(UpdateAidRequestAction::class)->execute($this->record, $dto);
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('تم تحديث طلب المساعدة بنجاح')
            ->success();
    }
}
