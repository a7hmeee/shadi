<?php

namespace App\Filament\Widgets;

use App\Domains\AidRequests\Enums\AidType;
use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\Identity\Enums\PermissionName;
use App\Filament\Pages\DecisionSupport;
use App\Filament\Resources\AidRequests\AidRequestResource;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class DuplicateAlertsWidget extends TableWidget
{
    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 2;

    public static function canView(): bool
    {
        return auth()->user()?->can(PermissionName::DECISION_SUPPORT_VIEW->value) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AidRequest::query()
                    ->with(['beneficiary.user'])
                    ->where('possible_duplicate', true)
                    ->orderByDesc('created_at')
                    ->limit(5),
            )
            ->heading('تنبيهات التكرار')
            ->description('آخر الطلبات المشتبه بتكرارها')
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
                TextColumn::make('priority_score')
                    ->label('درجة الاستحقاق')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 80 => 'danger',
                        $state >= 60 => 'warning',
                        $state >= 40 => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d'),
            ])
            ->recordActions([
                Action::make('review')
                    ->label('مراجعة')
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('warning')
                    ->url(fn (AidRequest $record) => AidRequestResource::getUrl('view', ['record' => $record])),
            ])
            ->headerActions([
                Action::make('decisionSupport')
                    ->label('مركز دعم القرار')
                    ->icon('heroicon-o-sparkles')
                    ->color('warning')
                    ->url(DecisionSupport::getUrl()),
            ])
            ->emptyStateHeading('لا توجد تنبيهات تكرار')
            ->emptyStateDescription('جميع الطلبات الحالية تبدو سليمة.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
