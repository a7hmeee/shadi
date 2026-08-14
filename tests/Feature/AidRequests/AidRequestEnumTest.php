<?php

namespace Tests\Feature\AidRequests;

use App\Domains\AidRequests\Enums\AidRequestStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AidRequestEnumTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_labels_are_arabic(): void
    {
        $this->assertEquals('مسودة', AidRequestStatus::DRAFT->label());
        $this->assertEquals('مقدم', AidRequestStatus::SUBMITTED->label());
        $this->assertEquals('قيد المراجعة', AidRequestStatus::UNDER_REVIEW->label());
        $this->assertEquals('بحاجة إلى معلومات', AidRequestStatus::NEEDS_INFORMATION->label());
        $this->assertEquals('مقبول', AidRequestStatus::APPROVED->label());
        $this->assertEquals('مرفوض', AidRequestStatus::REJECTED->label());
        $this->assertEquals('تم التسليم', AidRequestStatus::DELIVERED->label());
        $this->assertEquals('ملغي', AidRequestStatus::CANCELLED->label());
    }

    public function test_is_final_for_rejected_delivered_cancelled(): void
    {
        $this->assertTrue(AidRequestStatus::REJECTED->isFinal());
        $this->assertTrue(AidRequestStatus::DELIVERED->isFinal());
        $this->assertTrue(AidRequestStatus::CANCELLED->isFinal());
    }

    public function test_is_final_false_for_draft_submitted_under_review_needs_information_approved(): void
    {
        $this->assertFalse(AidRequestStatus::DRAFT->isFinal());
        $this->assertFalse(AidRequestStatus::SUBMITTED->isFinal());
        $this->assertFalse(AidRequestStatus::UNDER_REVIEW->isFinal());
        $this->assertFalse(AidRequestStatus::NEEDS_INFORMATION->isFinal());
        $this->assertFalse(AidRequestStatus::APPROVED->isFinal());
    }

    public function test_can_be_edited_by_beneficiary(): void
    {
        $this->assertTrue(AidRequestStatus::DRAFT->canBeEditedByBeneficiary());
        $this->assertTrue(AidRequestStatus::NEEDS_INFORMATION->canBeEditedByBeneficiary());
    }

    public function test_cannot_be_edited_by_beneficiary_for_other_statuses(): void
    {
        $this->assertFalse(AidRequestStatus::SUBMITTED->canBeEditedByBeneficiary());
        $this->assertFalse(AidRequestStatus::UNDER_REVIEW->canBeEditedByBeneficiary());
        $this->assertFalse(AidRequestStatus::APPROVED->canBeEditedByBeneficiary());
        $this->assertFalse(AidRequestStatus::REJECTED->canBeEditedByBeneficiary());
    }
}
