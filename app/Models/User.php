<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Domains\AidRequests\Models\AidDistribution;
use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\AidRequests\Models\AidRequestAttachment;
use App\Domains\Beneficiaries\Models\Beneficiary;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() !== 'admin') {
            return false;
        }

        return $this->hasAnyRole([
            \App\Domains\Identity\Enums\UserRole::SUPER_ADMIN->value,
            \App\Domains\Identity\Enums\UserRole::ADMIN->value,
            \App\Domains\Identity\Enums\UserRole::EMPLOYEE->value,
        ]);
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    public function beneficiary(): HasOne
    {
        return $this->hasOne(Beneficiary::class);
    }

    public function reviewedAidRequests(): HasMany
    {
        return $this->hasMany(AidRequest::class, 'reviewed_by');
    }

    public function decidedAidRequests(): HasMany
    {
        return $this->hasMany(AidRequest::class, 'decision_by');
    }

    public function uploadedAidRequestAttachments(): HasMany
    {
        return $this->hasMany(AidRequestAttachment::class, 'uploaded_by');
    }

    public function distributedAidRequests(): HasMany
    {
        return $this->hasMany(AidDistribution::class, 'distributed_by');
    }
}
