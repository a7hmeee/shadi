<?php

use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Enums\AidType;
use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\Beneficiaries\Models\Beneficiary;
use App\Domains\Eligibility\Actions\DetectAidRequestDuplicateAction;
use App\Domains\Eligibility\Services\DuplicateDetectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->detector = app(DuplicateDetectionService::class);
    $this->action = app(DetectAidRequestDuplicateAction::class);
});

function makeAidRequest(Beneficiary $beneficiary, AidType $aidType, ?string $submittedAt = null): AidRequest
{
    return AidRequest::create([
        'beneficiary_id' => $beneficiary->id,
        'request_number' => 'REQ-' . fake()->unique()->numerify('#######'),
        'aid_type' => $aidType,
        'title' => 'طلب مساعدة',
        'description' => 'وصف الطلب',
        'status' => AidRequestStatus::SUBMITTED,
        'submitted_at' => $submittedAt ?? now(),
    ]);
}

it('يكتشف التكرار عند تطابق نفس رقم الهوية', function () {
    $beneficiary = Beneficiary::factory()->create();

    $request1 = makeAidRequest($beneficiary, AidType::FOOD, now()->subDays(5));
    $request2 = makeAidRequest($beneficiary, AidType::MEDICAL);

    $result = $this->detector->detect($request2);

    expect($result->isDuplicate)->toBeTrue()
        ->and($result->matchedRequestId)->toBe($request1->id)
        ->and($result->reasons)->toContain('same_national_id');
});

it('يكتشف التكرار عند وجود طلب سابق لنفس المستفيد بنفس نوع المساعدة خلال 30 يوم', function () {
    $beneficiary = Beneficiary::factory()->create();

    $oldRequest = makeAidRequest($beneficiary, AidType::FOOD, now()->subDays(10));
    $currentRequest = makeAidRequest($beneficiary, AidType::FOOD);

    $result = $this->detector->detect($currentRequest);

    expect($result->score)->toBe(100)
        ->and($result->isDuplicate)->toBeTrue()
        ->and($result->matchedRequestId)->toBe($oldRequest->id)
        ->and($result->reasons)->toContain('recent_same_aid_type');
});

it('لا يكتشف تكرارًا عندما تكون البيانات مختلفة', function () {
    $user1 = \App\Models\User::factory()->create(['name' => 'أحمد محمد']);
    $user2 = \App\Models\User::factory()->create(['name' => 'خالد سعيد']);

    $beneficiary1 = Beneficiary::factory()->create([
        'user_id' => $user1->id,
        'national_id' => '11111111111111',
        'phone' => '01111111111',
        'alternative_phone' => '02222222222',
        'city' => 'صنعاء',
        'address' => 'شارع الزبيري',
    ]);

    $beneficiary2 = Beneficiary::factory()->create([
        'user_id' => $user2->id,
        'national_id' => '22222222222222',
        'phone' => '03333333333',
        'alternative_phone' => '04444444444',
        'city' => 'عدن',
        'address' => 'شارع الملكة أروى',
    ]);

    $request1 = makeAidRequest($beneficiary1, AidType::FOOD);
    $request2 = makeAidRequest($beneficiary2, AidType::MEDICAL);

    $result = $this->detector->detect($request2);

    expect($result->score)->toBe(0)
        ->and($result->isDuplicate)->toBeFalse()
        ->and($result->matchedRequestId)->toBeNull()
        ->and($result->reasons)->toBeEmpty();
});

it('يحدّث قاعدة البيانات عبر DetectAidRequestDuplicateAction', function () {
    $beneficiary = Beneficiary::factory()->create();

    $request1 = makeAidRequest($beneficiary, AidType::FOOD, now()->subDays(5));
    $request2 = makeAidRequest($beneficiary, AidType::MEDICAL);

    $result = $this->action->execute($request2);

    $request2->refresh();

    expect($result->isDuplicate)->toBeTrue()
        ->and($request2->possible_duplicate)->toBeTrue()
        ->and($request2->duplicate_of_request_id)->toBe($request1->id)
        ->and($request2->status)->toBe(AidRequestStatus::SUBMITTED);
});

it('لا يعتبر الهاتف المكرر في عمودين مختلفين تكرارًا', function () {
    $user1 = \App\Models\User::factory()->create(['name' => 'أحمد محمد']);
    $user2 = \App\Models\User::factory()->create(['name' => 'خالد سعيد']);

    $beneficiary1 = Beneficiary::factory()->create([
        'user_id' => $user1->id,
        'national_id' => '11111111111111',
        'phone' => '01111111111',
        'alternative_phone' => '03333333333',
        'city' => 'صنعاء',
        'address' => 'شارع الزبيري',
    ]);

    $beneficiary2 = Beneficiary::factory()->create([
        'user_id' => $user2->id,
        'national_id' => '22222222222222',
        'phone' => '03333333333',
        'alternative_phone' => '05555555555',
        'city' => 'عدن',
        'address' => 'شارع الملكة أروى',
    ]);

    $request1 = makeAidRequest($beneficiary1, AidType::FOOD);
    $request2 = makeAidRequest($beneficiary2, AidType::MEDICAL);

    $result = $this->detector->detect($request2);

    expect($result->score)->toBe(0)
        ->and($result->isDuplicate)->toBeFalse();
});
