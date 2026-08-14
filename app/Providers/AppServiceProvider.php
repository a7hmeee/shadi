<?php

namespace App\Providers;

use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\AidRequests\Models\AidRequestAttachment;
use App\Domains\AidRequests\Policies\AidRequestAttachmentPolicy;
use App\Domains\AidRequests\Policies\AidRequestPolicy;
use App\Domains\Beneficiaries\Models\Beneficiary;
use App\Domains\Beneficiaries\Policies\BeneficiaryPolicy;
use App\Domains\Identity\Enums\UserRole;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Contracts\Auth\Access\Authorizable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureLocale();
        $this->configurePolicies();
        $this->configureSuperAdminGate();
    }

    protected function configurePolicies(): void
    {
        Gate::policy(Beneficiary::class, BeneficiaryPolicy::class);
        Gate::policy(AidRequest::class, AidRequestPolicy::class);
        Gate::policy(AidRequestAttachment::class, AidRequestAttachmentPolicy::class);
    }

    protected function configureSuperAdminGate(): void
    {
        Gate::before(function (Authorizable $user, string $ability) {
            if ($user->hasRole(UserRole::SUPER_ADMIN->value)) {
                // forceDelete is deliberately disabled for everyone in the MVP.
                if ($ability === 'forceDelete') {
                    return null;
                }

                return true;
            }

            return null;
        });
    }

    protected function configureLocale(): void
    {
        app()->setLocale('ar');
        app()->setFallbackLocale('ar');
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
