<?php

namespace Tests\Feature\AidRequests;

use App\Domains\AidRequests\Actions\DeliverAidRequestAction;
use App\Domains\AidRequests\DTOs\AidDistributionData;
use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Models\AidDistribution;
use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\Identity\Enums\UserRole;
use App\Models\User;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    private function superAdmin(): User
    {
        return User::where('email', 'admin@aid.test')->first();
    }

    private function data(array $overrides = []): AidDistributionData
    {
        return AidDistributionData::fromArray(array_merge([
            'delivered_to_name' => 'أحمد محمد',
            'delivered_to_national_id' => '12345678901234',
            'delivery_method' => 'in_person',
            'amount' => 500.00,
            'delivered_at' => now()->toDateTimeString(),
            'receipt_number' => null,
            'notes' => 'تم التسليم يدويًا',
        ], $overrides));
    }

    public function test_approved_request_can_be_delivered(): void
    {
        $request = AidRequest::factory()->approved()->create();

        $distribution = app(DeliverAidRequestAction::class)->execute(
            $request,
            $this->data(),
            $this->superAdmin(),
        );

        $this->assertDatabaseHas('aid_distributions', [
            'id' => $distribution->id,
            'aid_request_id' => $request->id,
            'distributed_by' => $this->superAdmin()->id,
            'delivered_to_name' => 'أحمد محمد',
        ]);

        $request->refresh();

        $this->assertEquals(AidRequestStatus::DELIVERED, $request->status);
        $this->assertNotNull($request->delivered_at);
    }

    public function test_non_approved_requests_cannot_be_delivered(): void
    {
        foreach ([
            AidRequestStatus::SUBMITTED,
            AidRequestStatus::UNDER_REVIEW,
            AidRequestStatus::NEEDS_INFORMATION,
            AidRequestStatus::REJECTED,
            AidRequestStatus::DELIVERED,
            AidRequestStatus::CANCELLED,
        ] as $status) {
            $request = AidRequest::factory()->create(['status' => $status->value]);

            try {
                app(DeliverAidRequestAction::class)->execute(
                    $request,
                    $this->data(),
                    $this->superAdmin(),
                );

                $this->fail("Delivery should fail for status: {$status->value}");
            } catch (DomainException $e) {
                $this->assertNotEmpty($e->getMessage());
            }
        }

        $this->assertDatabaseCount('aid_distributions', 0);
    }

    public function test_cannot_deliver_same_request_twice(): void
    {
        $request = AidRequest::factory()->approved()->create();

        app(DeliverAidRequestAction::class)->execute($request, $this->data(), $this->superAdmin());

        try {
            app(DeliverAidRequestAction::class)->execute($request, $this->data(), $this->superAdmin());
            $this->fail('Second delivery should fail.');
        } catch (DomainException $e) {
            $this->assertNotEmpty($e->getMessage());
        }

        $this->assertEquals(1, AidDistribution::where('aid_request_id', $request->id)->count());
        $request->refresh();
        $this->assertEquals(AidRequestStatus::DELIVERED, $request->status);
    }

    public function test_employee_cannot_deliver(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole(UserRole::EMPLOYEE->value);

        $request = AidRequest::factory()->approved()->create();

        $this->assertFalse($employee->can('markDelivered', $request));

        $this->expectException(AuthorizationException::class);

        app(DeliverAidRequestAction::class)->execute($request, $this->data(), $employee);
    }

    public function test_admin_can_deliver(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::ADMIN->value);

        $request = AidRequest::factory()->approved()->create();

        $distribution = app(DeliverAidRequestAction::class)->execute($request, $this->data(), $admin);

        $this->assertDatabaseHas('aid_distributions', ['id' => $distribution->id]);
        $this->assertEquals($admin->id, $distribution->distributed_by);
        $request->refresh();
        $this->assertEquals(AidRequestStatus::DELIVERED, $request->status);
    }
}
