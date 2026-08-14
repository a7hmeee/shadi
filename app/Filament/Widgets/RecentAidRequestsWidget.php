<?php

namespace App\Filament\Widgets;

use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Enums\AidType;
use App\Domains\AidRequests\Enums\PriorityLevel;
use App\Domains\AidRequests\Models\AidRequest;
use App\Filament\Resources\AidRequests\AidRequestResource;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentAidRequestsWidget extends TableWidget
{
    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AidRequest::query()
                    ->with(['beneficiary.user'])
                    ->orderByDesc('created_at')
                    ->limit(5),
            )
            ->heading('أحدث الطلبات')
            ->description('آخر 5 طلبات مسجلة في النظام')
            ->paginated(false)
            ->columns([
                TextColumn::make('request_number')
                    ->label('رقم الطلب'),
                TextColumn::make('beneficiary.user.name')
                    ->label('المستفيد')
                    ->placeholder('—'),
                TextColumn::make('aid_type')
                    ->label('نوع المساعدة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof AidType ? $state->label() : $state),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof AidRequestStatus ? $state->label() : $state)
                    ->color(fn ($state) => match ($state) {
                        AidRequestStatus::DRAFT => 'gray',
                        AidRequestStatus::SUBMITTED => 'info',
                        AidRequestStatus::UNDER_REVIEW, AidRequestStatus::NEEDS_INFORMATION => 'warning',
                        AidRequestStatus::APPROVED, AidRequestStatus::DELIVERED => 'success',
                        AidRequestStatus::REJECTED => 'danger',
                        AidRequestStatus::CANCELLED => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('priority_level')
                    ->label('الأولوية')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof PriorityLevel ? $state->label() : $state)
                    ->color(fn ($state) => match ($state) {
                        PriorityLevel::LOW => 'gray',
                        PriorityLevel::MEDIUM => 'info',
                        PriorityLevel::HIGH => 'warning',
                        PriorityLevel::CRITICAL => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('submitted_at')
                    ->label('وقت التقديم')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('—'),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('عرض الطلب')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (AidRequest $record) => AidRequestResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateHeading('لا توجد بيانات حاليًا')
            ->emptyStateDescription('لم يتم تسجيل أي طلبات بعد.');
    }
}
