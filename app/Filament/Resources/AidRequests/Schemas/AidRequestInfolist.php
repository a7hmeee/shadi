<?php

namespace App\Filament\Resources\AidRequests\Schemas;

use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Enums\PriorityLevel;
use App\Domains\Eligibility\Services\DecisionSupportService;
use App\Domains\Eligibility\Services\DuplicateDetectionService;
use App\Domains\Eligibility\Services\PriorityCalculatorService;
use App\Filament\Resources\AidRequests\AidRequestResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AidRequestInfolist
{
    private const DUPLICATE_REASON_LABELS = [
        'same_national_id' => 'نفس رقم الهوية',
        'same_phone' => 'نفس رقم الهاتف',
        'same_address' => 'نفس المدينة والعنوان',
        'same_name' => 'نفس اسم المستفيد',
        'same_aid_type' => 'نفس نوع المساعدة',
        'recent_same_aid_type' => 'طلب سابق لنفس المستفيد بنفس نوع المساعدة خلال آخر 30 يوم',
    ];

    public static function configure(Schema $schema): Schema
    {
        $calculate = fn ($record) => app(PriorityCalculatorService::class)->calculate($record->beneficiary);
        $detect = fn ($record) => $record->duplicateResult ??= app(DuplicateDetectionService::class)->detect($record);
        $recommend = fn ($record) => $record->decisionRecommendation ??= app(DecisionSupportService::class)->recommend($record);

        return $schema
            ->components([
                Section::make('رقم الطلب')
                    ->schema([
                        TextEntry::make('request_number')
                            ->label('رقم الطلب')
                            ->badge(),
                    ]),

                Section::make('المستفيد')
                    ->schema([
                        TextEntry::make('beneficiary.user.name')
                            ->label('الاسم'),
                        TextEntry::make('beneficiary.national_id')
                            ->label('رقم الهوية'),
                        TextEntry::make('beneficiary.phone')
                            ->label('الهاتف'),
                        TextEntry::make('beneficiary.family_members_count')
                            ->label('عدد أفراد الأسرة'),
                        TextEntry::make('beneficiary.need_level')
                            ->label('مستوى الاحتياج')
                            ->badge()
                            ->formatStateUsing(fn ($record) => $record->beneficiary->need_level?->label()),
                    ]),

                Section::make('الطلب')
                    ->schema([
                        TextEntry::make('aid_type')
                            ->label('نوع المساعدة')
                            ->badge()
                            ->formatStateUsing(fn ($record) => $record->aid_type->label()),
                        TextEntry::make('description')
                            ->label('الوصف'),
                        TextEntry::make('requested_amount')
                            ->label('المبلغ المطلوب')
                            ->money('USD'),
                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->formatStateUsing(fn ($record) => $record->status->label())
                            ->color(fn ($record) => match ($record->status) {
                                AidRequestStatus::DRAFT => 'gray',
                                AidRequestStatus::SUBMITTED => 'info',
                                AidRequestStatus::UNDER_REVIEW => 'warning',
                                AidRequestStatus::NEEDS_INFORMATION => 'warning',
                                AidRequestStatus::APPROVED => 'success',
                                AidRequestStatus::REJECTED => 'danger',
                                AidRequestStatus::DELIVERED => 'success',
                                AidRequestStatus::CANCELLED => 'gray',
                                default => 'gray',
                            }),
                        TextEntry::make('priority_level')
                            ->label('الأولوية')
                            ->badge()
                            ->formatStateUsing(fn ($record) => $record->priority_level->label()),
                    ]),

                Section::make('تحذير التكرار')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->iconColor('danger')
                    ->schema([
                        TextEntry::make('duplicate_score')
                            ->label('درجة التكرار')
                            ->badge()
                            ->color('danger')
                            ->state(fn ($record) => $detect($record)->score.'%'),
                        TextEntry::make('duplicate_reasons')
                            ->label('أسباب الاشتباه')
                            ->listWithLineBreaks()
                            ->badge()
                            ->color('warning')
                            ->state(fn ($record) => collect($detect($record)->reasons)
                                ->map(fn ($reason) => self::DUPLICATE_REASON_LABELS[$reason] ?? $reason)
                                ->toArray()),
                        TextEntry::make('duplicatedFrom.request_number')
                            ->label('رقم الطلب المشابه')
                            ->badge()
                            ->color('danger')
                            ->visible(fn ($record) => $record->duplicatedFrom !== null),
                        TextEntry::make('duplicatedFrom.beneficiary.user.name')
                            ->label('المستفيد المرتبط بالطلب المشابه')
                            ->visible(fn ($record) => $record->duplicatedFrom !== null),
                        TextEntry::make('duplicatedFrom.aid_type')
                            ->label('نوع المساعدة')
                            ->badge()
                            ->formatStateUsing(fn ($record) => $record->duplicatedFrom->aid_type->label())
                            ->visible(fn ($record) => $record->duplicatedFrom !== null),
                        TextEntry::make('duplicatedFrom.submitted_at')
                            ->label('تاريخ الطلب السابق')
                            ->dateTime()
                            ->visible(fn ($record) => $record->duplicatedFrom !== null),
                        TextEntry::make('view_matched_request')
                            ->label(' ')
                            ->state('عرض الطلب المشابه')
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->color('info')
                            ->url(fn ($record) => $record->duplicatedFrom !== null
                                ? AidRequestResource::getUrl('view', ['record' => $record->duplicatedFrom])
                                : null),
                    ])
                    ->visible(fn ($record) => $record->possible_duplicate),

                Section::make('المراجعة')
                    ->schema([
                        TextEntry::make('reviewer.name')
                            ->label('المراجع')
                            ->visible(fn ($record) => $record->reviewer !== null),
                        TextEntry::make('reviewed_at')
                            ->label('وقت المراجعة')
                            ->dateTime()
                            ->visible(fn ($record) => $record->reviewed_at !== null),
                        TextEntry::make('reviewer_notes')
                            ->label('ملاحظات المراجع')
                            ->visible(fn ($record) => $record->reviewer_notes !== null),
                    ]),

                Section::make('القرار')
                    ->schema([
                        TextEntry::make('decisionMaker.name')
                            ->label('صاحب القرار')
                            ->visible(fn ($record) => $record->decisionMaker !== null),
                        TextEntry::make('approved_amount')
                            ->label('المبلغ الموافق عليه')
                            ->money('USD')
                            ->visible(fn ($record) => $record->approved_amount !== null),
                        TextEntry::make('rejection_reason')
                            ->label('سبب الرفض')
                            ->visible(fn ($record) => $record->rejection_reason !== null),
                        TextEntry::make('approved_at')
                            ->label('تاريخ الموافقة')
                            ->dateTime()
                            ->visible(fn ($record) => $record->approved_at !== null),
                        TextEntry::make('rejected_at')
                            ->label('تاريخ الرفض')
                            ->dateTime()
                            ->visible(fn ($record) => $record->rejected_at !== null),
                    ]),

                Section::make('المرفقات')
                    ->schema([
                        TextEntry::make('attachments_count')
                            ->label('المرفقات')
                            ->badge()
                            ->state(fn ($record) => $record->attachments()->count()),
                        TextEntry::make('verified_attachments_count')
                            ->label('المرفقات الموثقة')
                            ->badge()
                            ->color('success')
                            ->state(fn ($record) => $record->attachments()->where('is_verified', true)->count()),
                    ]),

                Section::make('التقييم الذكي للاستحقاق')
                    ->schema([
                        TextEntry::make('priority_score_display')
                            ->label('درجة الاستحقاق')
                            ->badge()
                            ->color('info')
                            ->state(fn ($record) => $record->priority_score !== null
                                ? $record->priority_score.' / 100'
                                : 'لم يتم حساب الأولوية بعد.'),
                        TextEntry::make('priority_level')
                            ->label('مستوى الأولوية')
                            ->badge()
                            ->visible(fn ($record) => $record->priority_level !== null)
                            ->formatStateUsing(fn ($record) => $record->priority_level->label())
                            ->color(fn ($record) => match ($record->priority_level) {
                                PriorityLevel::LOW => 'gray',
                                PriorityLevel::MEDIUM => 'info',
                                PriorityLevel::HIGH => 'warning',
                                PriorityLevel::CRITICAL => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('breakdown_income')
                            ->label('الدخل الشهري')
                            ->state(fn ($record) => '+'.$calculate($record)->breakdown['income'])
                            ->visible(fn ($record) => $record->priority_score !== null),
                        TextEntry::make('breakdown_family_size')
                            ->label('عدد أفراد الأسرة')
                            ->state(fn ($record) => '+'.$calculate($record)->breakdown['family_size'])
                            ->visible(fn ($record) => $record->priority_score !== null),
                        TextEntry::make('breakdown_disability')
                            ->label('الإعاقة')
                            ->state(fn ($record) => '+'.$calculate($record)->breakdown['disability'])
                            ->visible(fn ($record) => $record->priority_score !== null),
                        TextEntry::make('breakdown_chronic_disease')
                            ->label('مرض مزمن')
                            ->state(fn ($record) => '+'.$calculate($record)->breakdown['chronic_disease'])
                            ->visible(fn ($record) => $record->priority_score !== null),
                        TextEntry::make('breakdown_social_status')
                            ->label('الحالة الاجتماعية')
                            ->state(fn ($record) => '+'.$calculate($record)->breakdown['social_status'])
                            ->visible(fn ($record) => $record->priority_score !== null),
                        TextEntry::make('breakdown_housing')
                            ->label('حالة السكن')
                            ->state(fn ($record) => '+'.$calculate($record)->breakdown['housing'])
                            ->visible(fn ($record) => $record->priority_score !== null),
                        TextEntry::make('breakdown_need_level')
                            ->label('مستوى الاحتياج')
                            ->state(fn ($record) => '+'.$calculate($record)->breakdown['need_level'])
                            ->visible(fn ($record) => $record->priority_score !== null),
                    ]),

                Section::make('دعم القرار الذكي')
                    ->icon('heroicon-o-light-bulb')
                    ->iconColor('info')
                    ->schema([
                        TextEntry::make('decision_score')
                            ->label('درجة الاستحقاق')
                            ->badge()
                            ->color('info')
                            ->state(fn ($record) => $record->priority_score !== null
                                ? $record->priority_score.' / 100'
                                : 'لم يتم حسابها بعد'),
                        TextEntry::make('decision_priority_level')
                            ->label('مستوى الأولوية')
                            ->badge()
                            ->color(fn ($record) => match ($record->priority_level) {
                                PriorityLevel::CRITICAL => 'danger',
                                PriorityLevel::HIGH => 'warning',
                                PriorityLevel::MEDIUM => 'info',
                                default => 'gray',
                            })
                            ->state(fn ($record) => $record->priority_level?->label() ?? '—'),
                        TextEntry::make('decision_recommendation')
                            ->label('توصية النظام')
                            ->badge()
                            ->color(fn ($record) => match ($recommend($record)->recommendation) {
                                DecisionSupportService::RECOMMENDATION_REVIEW_DUPLICATE,
                                DecisionSupportService::RECOMMENDATION_URGENT_REVIEW => 'danger',
                                DecisionSupportService::RECOMMENDATION_HIGH_PRIORITY => 'warning',
                                DecisionSupportService::RECOMMENDATION_WAITING_INFORMATION => 'info',
                                default => 'gray',
                            })
                            ->state(fn ($record) => $recommend($record)->label),
                        TextEntry::make('decision_reasons')
                            ->label('أسباب التوصية')
                            ->listWithLineBreaks()
                            ->state(fn ($record) => $recommend($record)->reasons),
                        TextEntry::make('decision_warnings')
                            ->label('تحذيرات التكرار')
                            ->listWithLineBreaks()
                            ->badge()
                            ->color('warning')
                            ->state(fn ($record) => $recommend($record)->warnings)
                            ->visible(fn ($record) => $recommend($record)->warnings !== []),
                        TextEntry::make('decision_disclaimer')
                            ->label(' ')
                            ->icon('heroicon-o-information-circle')
                            ->color('gray')
                            ->state('هذه التوصية أداة مساعدة، والقرار النهائي يعود للموظف المختص.'),
                    ]),

                Section::make('بيانات التسليم')
                    ->schema([
                        TextEntry::make('distribution.delivered_to_name')
                            ->label('اسم المستلم'),
                        TextEntry::make('distribution.delivered_to_national_id')
                            ->label('رقم هوية المستلم')
                            ->placeholder('—'),
                        TextEntry::make('distribution.delivery_method')
                            ->label('طريقة التسليم')
                            ->placeholder('—'),
                        TextEntry::make('distribution.amount')
                            ->label('المبلغ')
                            ->money('USD')
                            ->placeholder('—'),
                        TextEntry::make('distribution.receipt_number')
                            ->label('رقم الإيصال')
                            ->placeholder('—'),
                        TextEntry::make('distribution.distributor.name')
                            ->label('الموظف الذي سلّم')
                            ->placeholder('—'),
                        TextEntry::make('distribution.delivered_at')
                            ->label('تاريخ التسليم')
                            ->dateTime(),
                        TextEntry::make('distribution.notes')
                            ->label('الملاحظات')
                            ->placeholder('—'),
                    ])
                    ->visible(fn ($record) => $record->distribution !== null),
            ]);
    }
}
