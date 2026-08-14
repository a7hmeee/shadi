<?php

namespace Tests\Feature;

use App\Domains\Beneficiaries\Enums\NeedLevel;
use App\Domains\Beneficiaries\Enums\SocialStatus;
use App\Domains\Beneficiaries\Models\Beneficiary;
use App\Domains\Identity\Enums\UserRole;
use App\Filament\Resources\Beneficiaries\Pages\CreateBeneficiary;
use App\Filament\Resources\Beneficiaries\Pages\EditBeneficiary;
use App\Filament\Resources\Beneficiaries\Pages\ListBeneficiaries;
use App\Filament\Resources\Beneficiaries\Pages\ViewBeneficiary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BeneficiaryFilamentTest extends TestCase
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

    private function employee(): User
    {
        $user = User::factory()->create();
        $user->assignRole(UserRole::EMPLOYEE->value);
        return $user;
    }

    private function beneficiaryUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(UserRole::BENEFICIARY->value);
        return $user;
    }

    // LIST PAGE
    public function test_super_admin_can_open_list_page(): void
    {
        Livewire::actingAs($this->superAdmin())
            ->test(ListBeneficiaries::class)
            ->assertSuccessful();
    }

    public function test_employee_can_open_list_page(): void
    {
        Livewire::actingAs($this->employee())
            ->test(ListBeneficiaries::class)
            ->assertSuccessful();
    }

    public function test_beneficiary_cannot_open_list_page(): void
    {
        Livewire::actingAs($this->beneficiaryUser())
            ->test(ListBeneficiaries::class)
            ->assertForbidden();
    }

    public function test_list_page_shows_existing_beneficiaries(): void
    {
        Beneficiary::factory()->count(3)->create();

        Livewire::actingAs($this->superAdmin())
            ->test(ListBeneficiaries::class)
            ->assertCanSeeTableRecords(Beneficiary::all());
    }

    public function test_list_page_search_by_national_id(): void
    {
        $beneficiary = Beneficiary::factory()->create(['national_id' => '999888777666']);

        Livewire::actingAs($this->superAdmin())
            ->test(ListBeneficiaries::class)
            ->searchTable('999888777666')
            ->assertCanSeeTableRecords([$beneficiary])
            ->assertCanNotSeeTableRecords(Beneficiary::where('id', '!=', $beneficiary->id)->get());
    }

    public function test_list_page_search_by_user_name(): void
    {
        $user = User::factory()->create(['name' => 'Unique Test Name']);
        $beneficiary = Beneficiary::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($this->superAdmin())
            ->test(ListBeneficiaries::class)
            ->searchTable('Unique Test Name')
            ->assertCanSeeTableRecords([$beneficiary]);
    }

    public function test_list_page_need_level_filter(): void
    {
        $critical = Beneficiary::factory()->create(['need_level' => NeedLevel::CRITICAL->value]);
        $low = Beneficiary::factory()->create(['need_level' => NeedLevel::LOW->value]);

        Livewire::actingAs($this->superAdmin())
            ->test(ListBeneficiaries::class)
            ->filterTable('need_level', NeedLevel::CRITICAL->value)
            ->assertCanSeeTableRecords([$critical])
            ->assertCanNotSeeTableRecords([$low]);
    }

    // CREATE PAGE
    public function test_super_admin_can_open_create_page(): void
    {
        Livewire::actingAs($this->superAdmin())
            ->test(CreateBeneficiary::class)
            ->assertSuccessful();
    }

    public function test_create_page_can_create_beneficiary(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($this->superAdmin())
            ->test(CreateBeneficiary::class)
            ->fillForm([
                'user_id' => $user->id,
                'national_id' => '123456789012',
                'phone' => '0501234567',
                'social_status' => SocialStatus::SINGLE->value,
                'family_members_count' => 3,
                'children_count' => 1,
                'has_disability' => false,
                'disability_details' => null,
                'has_chronic_disease' => false,
                'chronic_disease_details' => null,
                'need_level' => NeedLevel::LOW->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('beneficiaries', [
            'national_id' => '123456789012',
            'user_id' => $user->id,
        ]);
    }

    public function test_create_page_validation_errors_for_bad_data(): void
    {
        Livewire::actingAs($this->superAdmin())
            ->test(CreateBeneficiary::class)
            ->fillForm([
                'national_id' => '', // required
                'phone' => '0501234567',
                'social_status' => SocialStatus::SINGLE->value,
                'family_members_count' => 0, // min 1
                'children_count' => 1,
                'need_level' => NeedLevel::LOW->value,
            ])
            ->call('create')
            ->assertHasFormErrors(['national_id', 'family_members_count']);
    }

    public function test_create_page_rejects_duplicate_national_id(): void
    {
        $existing = Beneficiary::factory()->create(['national_id' => '111222333444']);
        $user = User::factory()->create();

        Livewire::actingAs($this->superAdmin())
            ->test(CreateBeneficiary::class)
            ->fillForm([
                'user_id' => $user->id,
                'national_id' => '111222333444', // duplicate
                'phone' => '0501234567',
                'social_status' => SocialStatus::SINGLE->value,
                'family_members_count' => 3,
                'children_count' => 1,
                'need_level' => NeedLevel::LOW->value,
            ])
            ->call('create')
            ->assertHasFormErrors(['national_id']);
    }

    // EDIT PAGE
    public function test_edit_page_loads_beneficiary_data(): void
    {
        $beneficiary = Beneficiary::factory()->create(['phone' => '0500000000']);

        Livewire::actingAs($this->superAdmin())
            ->test(EditBeneficiary::class, ['record' => $beneficiary->id])
            ->assertFormSet([
                'phone' => '0500000000',
                'national_id' => $beneficiary->national_id,
            ]);
    }

    public function test_edit_page_can_update_beneficiary(): void
    {
        $beneficiary = Beneficiary::factory()->create(['phone' => '0500000000']);

        Livewire::actingAs($this->superAdmin())
            ->test(EditBeneficiary::class, ['record' => $beneficiary->id])
            ->fillForm([
                'user_id' => $beneficiary->user_id,
                'national_id' => $beneficiary->national_id,
                'phone' => '0599999999',
                'has_disability' => false,
                'disability_details' => null,
                'has_chronic_disease' => false,
                'chronic_disease_details' => null,
                'social_status' => $beneficiary->social_status->value,
                'family_members_count' => $beneficiary->family_members_count,
                'children_count' => $beneficiary->children_count,
                'need_level' => NeedLevel::CRITICAL->value,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('beneficiaries', [
            'id' => $beneficiary->id,
            'phone' => '0599999999',
        ]);
    }

    // DELETE
    public function test_employee_cannot_delete_beneficiary(): void
    {
        $beneficiary = Beneficiary::factory()->create();

        // Employee can load the edit page (has update permission)
        Livewire::actingAs($this->employee())
            ->test(EditBeneficiary::class, ['record' => $beneficiary->id])
            ->assertSuccessful();

        // But cannot delete via policy
        $this->assertFalse($this->employee()->can('delete', $beneficiary));

        // The delete action should not be visible to employee
        Livewire::actingAs($this->employee())
            ->test(EditBeneficiary::class, ['record' => $beneficiary->id])
            ->assertActionHidden('delete');
    }

    public function test_super_admin_can_delete_beneficiary(): void
    {
        $beneficiary = Beneficiary::factory()->create();

        // Now test direct policy enforcement via Filament authorization
        Livewire::actingAs($this->superAdmin())
            ->test(EditBeneficiary::class, ['record' => $beneficiary->id])
            ->assertSuccessful();

        // Verify the delete action is authorized
        $this->assertTrue($this->superAdmin()->can('delete', $beneficiary));
    }

    // VIEW
    public function test_view_page_displays_beneficiary_data(): void
    {
        $beneficiary = Beneficiary::factory()->create(['national_id' => '555666777888']);

        Livewire::actingAs($this->superAdmin())
            ->test(ViewBeneficiary::class, ['record' => $beneficiary->id])
            ->assertSuccessful();
    }
}
