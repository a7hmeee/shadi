<?php

use App\Domains\AidRequests\Enums\PriorityLevel;
use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\Eligibility\Services\DecisionSupportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('يوصي بمراجعة عاجلة للطلبات الحرجة دون تكرار', function () {
    $request = AidRequest::factory()->create([
        'priority_level' => PriorityLevel::CRITICAL,
        'priority_score' => 87,
        'possible_duplicate' => false,
    ]);

    $result = app(DecisionSupportService::class)->recommend($request);

    expect($result->recommendation)->toBe(DecisionSupportService::RECOMMENDATION_URGENT_REVIEW)
        ->and($result->label)->toBe('مراجعة عاجلة')
        ->and($result->reasons)->toContain('درجة الاستحقاق 87/100');
});

it('يوصي بمراجعة التكرار أولًا عند الاشتباه بتكرار', function () {
    $request = AidRequest::factory()->create([
        'priority_level' => PriorityLevel::CRITICAL,
        'priority_score' => 90,
        'possible_duplicate' => true,
    ]);

    $result = app(DecisionSupportService::class)->recommend($request);

    expect($result->recommendation)->toBe(DecisionSupportService::RECOMMENDATION_REVIEW_DUPLICATE)
        ->and($result->label)->toBe('مراجعة التكرار أولًا')
        ->and($result->warnings)->toContain('يوجد احتمال تكرار في الطلب');
});
