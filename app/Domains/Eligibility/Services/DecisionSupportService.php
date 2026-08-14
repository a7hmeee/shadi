<?php

namespace App\Domains\Eligibility\Services;

use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Enums\PriorityLevel;
use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\Eligibility\DTOs\DecisionRecommendationData;

class DecisionSupportService
{
    public const RECOMMENDATION_REVIEW_DUPLICATE = 'REVIEW_DUPLICATE';

    public const RECOMMENDATION_URGENT_REVIEW = 'URGENT_REVIEW';

    public const RECOMMENDATION_HIGH_PRIORITY = 'HIGH_PRIORITY';

    public const RECOMMENDATION_WAITING_INFORMATION = 'WAITING_INFORMATION';

    public const RECOMMENDATION_NORMAL_REVIEW = 'NORMAL_REVIEW';

    private const RECOMMENDATION_LABELS = [
        self::RECOMMENDATION_REVIEW_DUPLICATE => 'مراجعة التكرار أولًا',
        self::RECOMMENDATION_URGENT_REVIEW => 'مراجعة عاجلة',
        self::RECOMMENDATION_HIGH_PRIORITY => 'أولوية مرتفعة',
        self::RECOMMENDATION_WAITING_INFORMATION => 'بانتظار استكمال المعلومات',
        self::RECOMMENDATION_NORMAL_REVIEW => 'مراجعة عادية',
    ];

    private const DUPLICATE_REASON_LABELS = [
        'same_national_id' => 'تطابق رقم الهوية',
        'same_phone' => 'تطابق رقم الهاتف',
        'same_address' => 'تطابق المدينة والعنوان',
        'same_name' => 'تطابق اسم المستفيد',
        'same_aid_type' => 'نفس نوع المساعدة',
        'recent_same_aid_type' => 'نفس نوع المساعدة خلال آخر 30 يومًا',
    ];

    private const PRIORITY_REASON_LABELS = [
        'income' => 'دخل الأسرة منخفض',
        'family_size' => 'عدد أفراد الأسرة مرتفع',
        'disability' => 'يوجد فرد من ذوي الإعاقة',
        'chronic_disease' => 'يوجد مرض مزمن',
        'social_status' => 'الحالة الاجتماعية تزيد الاستحقاق',
        'housing' => 'حالة السكن صعبة',
        'need_level' => 'مستوى الاحتياج مرتفع',
    ];

    public function __construct(
        private readonly DuplicateDetectionService $duplicateDetector,
        private readonly PriorityCalculatorService $priorityCalculator,
    ) {}

    public function recommend(AidRequest $aidRequest): DecisionRecommendationData
    {
        $reasons = [];
        $warnings = [];

        $this->addPriorityReasons($aidRequest, $reasons);

        if ($aidRequest->possible_duplicate) {
            $recommendation = self::RECOMMENDATION_REVIEW_DUPLICATE;
            $this->addDuplicateWarnings($aidRequest, $warnings);
        } elseif ($aidRequest->priority_level === PriorityLevel::CRITICAL) {
            $recommendation = self::RECOMMENDATION_URGENT_REVIEW;
        } elseif ($aidRequest->priority_level === PriorityLevel::HIGH) {
            $recommendation = self::RECOMMENDATION_HIGH_PRIORITY;
        } elseif ($aidRequest->status === AidRequestStatus::NEEDS_INFORMATION) {
            $recommendation = self::RECOMMENDATION_WAITING_INFORMATION;
        } else {
            $recommendation = self::RECOMMENDATION_NORMAL_REVIEW;
        }

        return new DecisionRecommendationData(
            recommendation: $recommendation,
            label: self::RECOMMENDATION_LABELS[$recommendation],
            reasons: $reasons,
            warnings: $warnings,
        );
    }

    /**
     * @param  string[]  $reasons
     */
    private function addPriorityReasons(AidRequest $request, array &$reasons): void
    {
        $reasons[] = $request->priority_score !== null
            ? "درجة الاستحقاق {$request->priority_score}/100"
            : 'لم يتم حساب درجة الاستحقاق بعد';

        if ($request->priority_level !== null) {
            $reasons[] = 'مستوى الأولوية: '.$request->priority_level->label();
        }

        $beneficiary = $request->beneficiary()->first();

        if ($beneficiary === null) {
            return;
        }

        $breakdown = $this->priorityCalculator->calculate($beneficiary)->breakdown;

        foreach (self::PRIORITY_REASON_LABELS as $key => $label) {
            if (($breakdown[$key] ?? 0) > 0) {
                $reasons[] = $label;
            }
        }

        if ($beneficiary->need_level !== null) {
            $reasons[] = 'مستوى الاحتياج: '.$beneficiary->need_level->label();
        }
    }

    /**
     * @param  string[]  $warnings
     */
    private function addDuplicateWarnings(AidRequest $request, array &$warnings): void
    {
        $warnings[] = 'يوجد احتمال تكرار في الطلب';

        if ($request->duplicatedFrom?->request_number !== null) {
            $warnings[] = 'يوجد طلب مشابه: '.$request->duplicatedFrom->request_number;
        }

        $result = $this->duplicateDetector->detect($request);

        foreach ($result->reasons as $reason) {
            $warnings[] = self::DUPLICATE_REASON_LABELS[$reason] ?? $reason;
        }
    }
}
