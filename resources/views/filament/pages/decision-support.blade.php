<x-filament-panels::page>
    @php
        $statusColor = fn (App\Domains\AidRequests\Enums\AidRequestStatus $state) => match ($state) {
            App\Domains\AidRequests\Enums\AidRequestStatus::DRAFT => 'gray',
            App\Domains\AidRequests\Enums\AidRequestStatus::SUBMITTED => 'info',
            App\Domains\AidRequests\Enums\AidRequestStatus::UNDER_REVIEW => 'warning',
            App\Domains\AidRequests\Enums\AidRequestStatus::NEEDS_INFORMATION => 'warning',
            App\Domains\AidRequests\Enums\AidRequestStatus::APPROVED => 'success',
            App\Domains\AidRequests\Enums\AidRequestStatus::REJECTED => 'danger',
            App\Domains\AidRequests\Enums\AidRequestStatus::DELIVERED => 'success',
            App\Domains\AidRequests\Enums\AidRequestStatus::CANCELLED => 'gray',
            default => 'gray',
        };

        $priorityColor = fn ($level) => match ($level) {
            App\Domains\AidRequests\Enums\PriorityLevel::CRITICAL => 'danger',
            App\Domains\AidRequests\Enums\PriorityLevel::HIGH => 'warning',
            App\Domains\AidRequests\Enums\PriorityLevel::MEDIUM => 'info',
            App\Domains\AidRequests\Enums\PriorityLevel::LOW => 'gray',
            default => 'gray',
        };

        $kpiStyles = [
            ['bg' => '#FFF7ED', 'border' => '#F97316', 'text' => '#C2410C', 'icon_bg' => '#FFEDD5'],
            ['bg' => '#FEF2F2', 'border' => '#EF4444', 'text' => '#B91C1C', 'icon_bg' => '#FEE2E2'],
            ['bg' => '#FFFBEB', 'border' => '#F59E0B', 'text' => '#B45309', 'icon_bg' => '#FEF3C7'],
            ['bg' => '#FFFBEB', 'border' => '#EAB308', 'text' => '#A16207', 'icon_bg' => '#FEF9C3'],
            ['bg' => '#F0F9FF', 'border' => '#0EA5E9', 'text' => '#0369A1', 'icon_bg' => '#E0F2FE'],
            ['bg' => '#F0FDF4', 'border' => '#22C55E', 'text' => '#15803D', 'icon_bg' => '#DCFCE7'],
        ];

        $scoreColor = fn ($score) => match (true) {
            $score >= 80 => '#EF4444',
            $score >= 60 => '#F59E0B',
            $score >= 40 => '#3B82F6',
            default => '#9CA3AF',
        };
    @endphp

    <div style="direction: rtl;">

        {{-- KPI CARDS --}}
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
            @foreach ($this->stats as $i => $stat)
                @php $s = $kpiStyles[$i] ?? $kpiStyles[0]; @endphp
                <div style="background: {{ $s['bg'] }}; border: 1px solid {{ $s['border'] }}22; border-radius: 16px; padding: 1.25rem; position: relative; overflow: hidden;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="font-size: 0.8rem; color: {{ $s['text'] }}; margin: 0 0 0.25rem 0; font-weight: 500;">{{ $stat['label'] }}</p>
                            <p style="font-size: 2rem; font-weight: 800; color: {{ $s['text'] }}; margin: 0; line-height: 1.1;">{{ $stat['count'] }}</p>
                        </div>
                        <div style="width: 48px; height: 48px; background: {{ $s['icon_bg'] }}; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <x-filament::icon :icon="$stat['icon']" style="width: 24px; height: 24px; color: {{ $s['text'] }};" />
                        </div>
                    </div>
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 4px; background: {{ $s['border'] }};"></div>
                </div>
            @endforeach
        </div>

        {{-- PRIORITY TABLE --}}
        <x-filament::section heading="الحالات الأعلى أولوية" description="مرتبة حسب درجة الاستحقاق — استبعدت المرفوضة والملغاة والمُسلَّمة">
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem;">
                @foreach ($this->filterOptions as $value => $label)
                    <button
                        type="button"
                        wire:click="setFilter('{{ $value }}')"
                        style="border-radius: 9999px; padding: 0.375rem 1rem; font-size: 0.75rem; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; {{ $this->activeFilter === $value ? 'background: #2563EB; color: white; box-shadow: 0 1px 3px rgba(37,99,235,0.3);' : 'background: #F3F4F6; color: #4B5563;' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <div style="overflow-x: auto; border: 1px solid #E5E7EB; border-radius: 12px;">
                <table style="width: 100%; text-align: right; font-size: 0.875rem; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #F9FAFB; border-bottom: 2px solid #E5E7EB;">
                            <th style="padding: 0.75rem 1rem; font-weight: 600; color: #6B7280; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">رقم الطلب</th>
                            <th style="padding: 0.75rem 1rem; font-weight: 600; color: #6B7280; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">المستفيد</th>
                            <th style="padding: 0.75rem 1rem; font-weight: 600; color: #6B7280; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">النوع</th>
                            <th style="padding: 0.75rem 1rem; font-weight: 600; color: #6B7280; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">الدرجة</th>
                            <th style="padding: 0.75rem 1rem; font-weight: 600; color: #6B7280; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">الأولوية</th>
                            <th style="padding: 0.75rem 1rem; font-weight: 600; color: #6B7280; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">الحالة</th>
                            <th style="padding: 0.75rem 1rem; font-weight: 600; color: #6B7280; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">التكرار</th>
                            <th style="padding: 0.75rem 1rem; font-weight: 600; color: #6B7280; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">التاريخ</th>
                            <th style="padding: 0.75rem 1rem; font-weight: 600; color: #6B7280; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->topPriorityRequests as $request)
                            <tr style="border-bottom: 1px solid #F3F4F6; transition: background 0.15s;" onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 0.75rem 1rem;">
                                    <a href="{{ $this->viewRequestUrl($request->id) }}" style="font-weight: 600; color: #2563EB; text-decoration: none;">{{ $request->request_number }}</a>
                                </td>
                                <td style="padding: 0.75rem 1rem; font-weight: 500; color: #111827;">{{ $request->beneficiary?->user?->name ?? '—' }}</td>
                                <td style="padding: 0.75rem 1rem;">
                                    <span style="background: #F3F4F6; padding: 0.25rem 0.625rem; border-radius: 6px; font-size: 0.75rem; font-weight: 500; color: #374151;">{{ $request->aid_type?->label() ?? '—' }}</span>
                                </td>
                                <td style="padding: 0.75rem 1rem;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <div style="width: 64px; height: 6px; background: #E5E7EB; border-radius: 3px; overflow: hidden;">
                                            <div style="width: {{ $request->priority_score ?? 0 }}%; height: 100%; background: {{ $scoreColor($request->priority_score ?? 0) }}; border-radius: 3px;"></div>
                                        </div>
                                        <span style="font-size: 0.75rem; font-weight: 700; color: #374151;">{{ $request->priority_score ?? '—' }}</span>
                                    </div>
                                </td>
                                <td style="padding: 0.75rem 1rem;">
                                    <x-filament::badge :color="$priorityColor($request->priority_level)">{{ $request->priority_level?->label() ?? '—' }}</x-filament::badge>
                                </td>
                                <td style="padding: 0.75rem 1rem;">
                                    <x-filament::badge :color="$statusColor($request->status)">{{ $request->status->label() }}</x-filament::badge>
                                </td>
                                <td style="padding: 0.75rem 1rem;">
                                    @if ($request->possible_duplicate)
                                        <x-filament::badge color="warning" icon="heroicon-o-exclamation-triangle">مشتبه</x-filament::badge>
                                    @else
                                        <span style="color: #D1D5DB;">—</span>
                                    @endif
                                </td>
                                <td style="padding: 0.75rem 1rem; color: #6B7280;">{{ $request->submitted_at?->format('Y-m-d') ?? '—' }}</td>
                                <td style="padding: 0.75rem 1rem;">
                                    <a href="{{ $this->viewRequestUrl($request->id) }}" style="color: #2563EB; font-weight: 500; text-decoration: none; font-size: 0.8125rem;">عرض ←</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="padding: 3rem; text-align: center; color: #9CA3AF;">لا توجد طلبات مطابقة</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- DUPLICATE ALERTS --}}
        <x-filament::section heading="تنبيهات التكرار" description="طلبات مشتبه بوجود طلب مكرر لها">
            <div style="overflow-x: auto; border: 1px solid #E5E7EB; border-radius: 12px;">
                <table style="width: 100%; text-align: right; font-size: 0.875rem; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #FFFBEB; border-bottom: 2px solid #FDE68A;">
                            <th style="padding: 0.75rem 1rem; font-weight: 600; color: #92400E; font-size: 0.75rem;">الطلب الحالي</th>
                            <th style="padding: 0.75rem 1rem; font-weight: 600; color: #92400E; font-size: 0.75rem;">المستفيد</th>
                            <th style="padding: 0.75rem 1rem; font-weight: 600; color: #92400E; font-size: 0.75rem;">الطلب المشابه</th>
                            <th style="padding: 0.75rem 1rem; font-weight: 600; color: #92400E; font-size: 0.75rem;">النوع</th>
                            <th style="padding: 0.75rem 1rem; font-weight: 600; color: #92400E; font-size: 0.75rem;">الحالة</th>
                            <th style="padding: 0.75rem 1rem; font-weight: 600; color: #92400E; font-size: 0.75rem;">الأولوية</th>
                            <th style="padding: 0.75rem 1rem; font-weight: 600; color: #92400E; font-size: 0.75rem;">إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->duplicateAlerts as $request)
                            <tr style="border-bottom: 1px solid #F3F4F6;" onmouseover="this.style.background='#FFFBEB'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 0.75rem 1rem;">
                                    <a href="{{ $this->viewRequestUrl($request->id) }}" style="font-weight: 600; color: #2563EB; text-decoration: none;">{{ $request->request_number }}</a>
                                </td>
                                <td style="padding: 0.75rem 1rem; font-weight: 500; color: #111827;">{{ $request->beneficiary?->user?->name ?? '—' }}</td>
                                <td style="padding: 0.75rem 1rem;">
                                    @if ($request->duplicatedFrom)
                                        <a href="{{ $this->viewRequestUrl($request->duplicatedFrom->id) }}" style="font-weight: 600; color: #D97706; text-decoration: none;">{{ $request->duplicatedFrom->request_number }}</a>
                                    @else
                                        <span style="color: #D1D5DB;">—</span>
                                    @endif
                                </td>
                                <td style="padding: 0.75rem 1rem;">
                                    <span style="background: #F3F4F6; padding: 0.25rem 0.625rem; border-radius: 6px; font-size: 0.75rem; font-weight: 500; color: #374151;">{{ $request->aid_type?->label() ?? '—' }}</span>
                                </td>
                                <td style="padding: 0.75rem 1rem;">
                                    <x-filament::badge :color="$statusColor($request->status)">{{ $request->status->label() }}</x-filament::badge>
                                </td>
                                <td style="padding: 0.75rem 1rem;">
                                    <x-filament::badge :color="$priorityColor($request->priority_level)">{{ $request->priority_level?->label() ?? '—' }}</x-filament::badge>
                                </td>
                                <td style="padding: 0.75rem 1rem;">
                                    <a href="{{ $this->viewRequestUrl($request->id) }}" style="color: #D97706; font-weight: 500; text-decoration: none; font-size: 0.8125rem;">مقارنة ←</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding: 3rem; text-align: center; color: #9CA3AF;">لا توجد تنبيهات تكرار حاليًا</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- INFO --}}
        <div style="display: flex; align-items: flex-start; gap: 0.75rem; background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 12px; padding: 1rem; margin-top: 1.5rem;">
            <x-filament::icon icon="heroicon-o-information-circle" style="width: 20px; height: 20px; color: #3B82F6; flex-shrink: 0; margin-top: 2px;" />
            <p style="font-size: 0.875rem; color: #1E40AF; margin: 0;">هذه الشاشة أداة دعم قرار فقط — لا يتم اتخاذ أي موافقة أو رفض أو حذف تلقائي، والقرار النهائي يعود للموظف المختص.</p>
        </div>
    </div>
</x-filament-panels::page>
