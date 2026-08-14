<?php

namespace App\Filament\Widgets;

use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Enums\AidType;
use App\Domains\AidRequests\Enums\PriorityLevel;
use App\Domains\AidRequests\Models\AidRequest;
use App\Filament\Pages\DecisionSupport;
use App\Filament\Resources\AidRequests\AidRequestResource;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TopPriorityRequestsWidget extends TableWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AidRequest::query()
                    ->with(['beneficiary.user'])
                    ->whereNotIn('status', [
                        AidRequestStatus::DELIVERED->value,
                        AidRequestStatus::REJECTED->value,
                        AidRequestStatus::CANCELLED->value,
                    ])
                    ->whereNotNull('priority_score')
                    ->orderByDesc('priority_score')
                    ->orderByDesc('submitted_at')
                    ->limit(5),
            )
            ->heading('أعلى الطلبات أولوية')
            ->description('أهم 5 طلبات تحتاج انتباهك الآن')
            ->paginated(false)
            ->columns([
                TextColumn::make('request_number')
                    ->label('رقم الطلب')
                    ->searchable(),
                TextColumn::make('beneficiary.user.name')
                    ->label('المستفيد')
                    ->placeholder('—'),
                TextColumn::make('aid_type')
                    ->label('نوع المساعدة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof AidType ? $state->label() : $state),
                TextColumn::make('priority_score')
                    ->label('درجة الاستحقاق')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 80 => 'danger',
                        $state >= 60 => 'warning',
                        $state >= 40 => 'info',
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
                TextColumn::make('possible_duplicate')
                    ->label('تكرار محتمل')
                    ->badge()
                    ->formatStateUsing(fn (bool $state) => $state ? 'نعم' : 'لا')
                    ->color(fn (bool $state) => $state ? 'warning' : 'gray')
                    ->icon(fn (bool $state) => $state ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle'),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (AidRequest $record) => AidRequestResource::getUrl('view', ['record' => $record])),
            ])
            ->headerActions([
                Action::make('decisionSupport')
                    ->label('مركز دعم القرار')
                    ->icon('heroicon-o-sparkles')
                    ->color('warning')
                    ->url(DecisionSupport::getUrl()),
            ])
            ->emptyStateHeading('لا توجد بيانات حاليًا')
            ->emptyStateDescription('لم يتم العثور على طلبات ذات أولوية محسوبة.');
    }
}
