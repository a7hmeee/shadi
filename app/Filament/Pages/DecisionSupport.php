<?php

namespace App\Filament\Pages;

use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Enums\PriorityLevel;
use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\Identity\Enums\PermissionName;
use App\Filament\Resources\AidRequests\AidRequestResource;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use UnitEnum;

class DecisionSupport extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'مركز دعم القرار';

    protected static ?string $title = 'مركز دعم القرار';

    protected static string|UnitEnum|null $navigationGroup = 'التحليل الذكي';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.decision-support';

    public string $activeFilter = 'all';

    public static function canAccess(): bool
    {
        return auth()->user()?->can(PermissionName::DECISION_SUPPORT_VIEW->value) ?? false;
    }

    public function setFilter(string $filter): void
    {
        $this->activeFilter = $filter;
    }

    public function viewRequestUrl(int $id): string
    {
        return AidRequestResource::getUrl('view', ['record' => $id]);
    }

    /**
     * @return array<int, array{label: string, count: int, icon: Heroicon, color: string, iconClass: string}>
     */
    public function getStatsProperty(): array
    {
        return [
            [
                'label' => 'إجمالي الطلبات قيد المعالجة',
                'count' => AidRequest::whereIn('status', [
                    AidRequestStatus::SUBMITTED->value,
                    AidRequestStatus::UNDER_REVIEW->value,
                ])->count(),
                'icon' => Heroicon::OutlinedClipboardDocumentList,
                'color' => 'primary',
                'iconClass' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
            ],
            [
                'label' => 'الطلبات الحرجة',
                'count' => $this->activeRequestsQuery()
                    ->where('priority_level', PriorityLevel::CRITICAL->value)
                    ->count(),
                'icon' => Heroicon::OutlinedExclamationTriangle,
                'color' => 'danger',
                'iconClass' => 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
            ],
            [
                'label' => 'الطلبات عالية الأولوية',
                'count' => $this->activeRequestsQuery()
                    ->where('priority_level', PriorityLevel::HIGH->value)
                    ->count(),
                'icon' => Heroicon::OutlinedArrowTrendingUp,
                'color' => 'warning',
                'iconClass' => 'bg-yellow-50 text-yellow-600 dark:bg-yellow-500/10 dark:text-yellow-400',
            ],
            [
                'label' => 'طلبات مشتبه بتكرارها',
                'count' => AidRequest::where('possible_duplicate', true)->count(),
                'icon' => Heroicon::OutlinedMagnifyingGlass,
                'color' => 'warning',
                'iconClass' => 'bg-yellow-50 text-yellow-600 dark:bg-yellow-500/10 dark:text-yellow-400',
            ],
            [
                'label' => 'طلبات تحتاج معلومات إضافية',
                'count' => AidRequest::where('status', AidRequestStatus::NEEDS_INFORMATION->value)->count(),
                'icon' => Heroicon::OutlinedQuestionMarkCircle,
                'color' => 'info',
                'iconClass' => 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400',
            ],
            [
                'label' => 'معتمدة بانتظار التسليم',
                'count' => AidRequest::where('status', AidRequestStatus::APPROVED->value)
                    ->whereDoesntHave('distribution')
                    ->count(),
                'icon' => Heroicon::OutlinedTruck,
                'color' => 'success',
                'iconClass' => 'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function getFilterOptionsProperty(): array
    {
        return [
            'all' => 'الكل',
            'critical' => 'الحرجة',
            'high' => 'الأولوية المرتفعة',
            'duplicate' => 'تكرار محتمل',
            'needs_information' => 'بحاجة معلومات',
            'approved' => 'معتمدة بانتظار التسليم',
        ];
    }

    public function getTopPriorityRequestsProperty(): Collection
    {
        return $this->priorityQuery()
            ->limit(10)
            ->get();
    }

    public function getDuplicateAlertsProperty(): Collection
    {
        return AidRequest::query()
            ->with(['beneficiary.user', 'duplicatedFrom'])
            ->where('possible_duplicate', true)
            ->orderByDesc('priority_score')
            ->limit(10)
            ->get();
    }

    private function priorityQuery(): Builder
    {
        return $this->activeRequestsQuery()
            ->with(['beneficiary.user', 'duplicatedFrom'])
            ->when(
                $this->activeFilter === 'critical',
                fn (Builder $query) => $query->where('priority_level', PriorityLevel::CRITICAL->value),
            )
            ->when(
                $this->activeFilter === 'high',
                fn (Builder $query) => $query->where('priority_level', PriorityLevel::HIGH->value),
            )
            ->when(
                $this->activeFilter === 'duplicate',
                fn (Builder $query) => $query->where('possible_duplicate', true),
            )
            ->when(
                $this->activeFilter === 'needs_information',
                fn (Builder $query) => $query->where('status', AidRequestStatus::NEEDS_INFORMATION->value),
            )
            ->when(
                $this->activeFilter === 'approved',
                fn (Builder $query) => $query
                    ->where('status', AidRequestStatus::APPROVED->value)
                    ->whereDoesntHave('distribution'),
            )
            ->orderByDesc('priority_score');
    }

    private function activeRequestsQuery(): Builder
    {
        return AidRequest::query()->whereNotIn('status', [
            AidRequestStatus::DELIVERED->value,
            AidRequestStatus::REJECTED->value,
            AidRequestStatus::CANCELLED->value,
        ]);
    }
}
