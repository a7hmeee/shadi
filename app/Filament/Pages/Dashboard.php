<?php

namespace App\Filament\Pages;

use App\Domains\Identity\Enums\PermissionName;
use App\Filament\Resources\AidRequests\AidRequestResource;
use App\Filament\Resources\Beneficiaries\BeneficiaryResource;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'لوحة التحكم';

    protected static ?string $navigationLabel = 'لوحة التحكم';

    public function getColumns(): int|array
    {
        return 4;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createBeneficiary')
                ->label('إضافة مستفيد')
                ->icon(Heroicon::OutlinedPlusCircle)
                ->url(BeneficiaryResource::getUrl('create')),
            Action::make('createAidRequest')
                ->label('طلب مساعدة جديد')
                ->icon(Heroicon::OutlinedDocumentPlus)
                ->color('info')
                ->url(AidRequestResource::getUrl('create')),
            Action::make('decisionSupport')
                ->label('مركز دعم القرار')
                ->icon(Heroicon::OutlinedSparkles)
                ->color('warning')
                ->visible(fn () => auth()->user()?->can(PermissionName::DECISION_SUPPORT_VIEW->value) ?? false)
                ->url(DecisionSupport::getUrl()),
        ];
    }
}
