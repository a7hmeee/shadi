<?php

namespace Tests\Feature;

use App\Domains\AidRequests\Enums\PriorityLevel;
use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\Identity\Enums\UserRole;
use App\Models\User;
use Filament\Pages\Dashboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    public function test_dashboard_loads_with_priority_widget(): void
    {
        $admin = User::where('email', 'admin@aid.test')->first();

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->assertSuccessful();
    }

    public function test_dashboard_counts_priority_requests(): void
    {
        $admin = User::where('email', 'admin@aid.test')->first();

        AidRequest::factory()->count(3)->create(['priority_level' => PriorityLevel::CRITICAL->value]);
        AidRequest::factory()->count(2)->create(['priority_level' => PriorityLevel::HIGH->value]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\PriorityOverviewWidget::class)
            ->assertSuccessful();
    }
}
