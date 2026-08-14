<?php

namespace App\Domains\Eligibility\Services;

use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\Beneficiaries\Models\Beneficiary;
use App\Domains\Eligibility\DTOs\DuplicateDetectionResultData;
use Illuminate\Support\Carbon;

class DuplicateDetectionService
{
    public const THRESHOLD = 70;

    public function detect(AidRequest $aidRequest): DuplicateDetectionResultData
    {
        $beneficiary = $aidRequest->beneficiary()->with('user')->first();

        $candidates = AidRequest::query()
            ->where('id', '!=', $aidRequest->id)
            ->get();

        $beneficiaries = Beneficiary::withTrashed()
            ->with('user')
            ->whereIn('id', $candidates->pluck('beneficiary_id'))
            ->get()
            ->keyBy('id');

        foreach ($candidates as $candidate) {
            $candidate->setRelation('beneficiary', $beneficiaries->get($candidate->beneficiary_id));
        }

        $bestScore = 0;
        $bestReasons = [];
        $bestCandidateId = null;

        foreach ($candidates as $candidate) {
            [$score, $reasons] = $this->scoreCandidate(
                $aidRequest,
                $beneficiary,
                $candidate,
                $candidate->beneficiary,
            );

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestReasons = $reasons;
                $bestCandidateId = $candidate->id;
            }
        }

        $cappedScore = min(100, $bestScore);

        return DuplicateDetectionResultData::fromScore(
            $cappedScore,
            $cappedScore >= self::THRESHOLD,
            $bestCandidateId,
            $bestReasons,
        );
    }

    /**
     * @return array{0: int, 1: string[]}
     */
    private function scoreCandidate(
        AidRequest $current,
        ?Beneficiary $currentBeneficiary,
        AidRequest $candidate,
        ?Beneficiary $candidateBeneficiary,
    ): array {
        $score = 0;
        $reasons = [];

            if ($currentBeneficiary !== null && $candidateBeneficiary !== null) {
                if ($this->same($currentBeneficiary->national_id, $candidateBeneficiary->national_id)) {
                    $score += 70;
                    $reasons[] = 'same_national_id';
                }

                if ($this->same($currentBeneficiary->phone, $candidateBeneficiary->phone)) {
                    $score += 20;
                    $reasons[] = 'same_phone';
                }

                if ($this->sameAddress($currentBeneficiary, $candidateBeneficiary)) {
                    $score += 10;
                    $reasons[] = 'same_address';
                }

                if ($currentBeneficiary->user?->id !== null
                    && $candidateBeneficiary->user?->id !== null
                    && $this->same($currentBeneficiary->user->name, $candidateBeneficiary->user->name)
                    && $currentBeneficiary->user->id !== $candidateBeneficiary->user->id) {
                    $score += 10;
                    $reasons[] = 'same_name';
                }

                if ($candidate->beneficiary?->id === $currentBeneficiary->id
                    && $candidate->aid_type === $current->aid_type
                    && $this->isRecent($candidate)) {
                    $score += 30;
                    $reasons[] = 'recent_same_aid_type';
                }
            }

        if ($candidate->aid_type === $current->aid_type) {
            $score += 10;
            $reasons[] = 'same_aid_type';
        }

        return [$score, $reasons];
    }

    private function same(?string $a, ?string $b): bool
    {
        if ($a === null || $b === null) {
            return false;
        }

        return $this->normalize($a) === $this->normalize($b);
    }

    private function sameAddress(Beneficiary $a, Beneficiary $b): bool
    {
        if ($a->city === null || $b->city === null || $a->address === null || $b->address === null) {
            return false;
        }

        return $this->normalize($a->city) === $this->normalize($b->city)
            && $this->normalize($a->address) === $this->normalize($b->address);
    }

    private function isRecent(AidRequest $candidate): bool
    {
        $date = $candidate->submitted_at ?? $candidate->created_at;

        return $date !== null && $date->gte(Carbon::now()->subDays(30));
    }

    private function normalize(string $value): string
    {
        return strtolower(preg_replace('/\s+/', '', trim($value)) ?? '');
    }
}
