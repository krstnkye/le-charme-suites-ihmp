<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\PermissionService;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'role', 'approved_at', 'password', 'ui_preferences', 'profile_photo_path'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'password' => 'hashed',
            'ui_preferences' => 'array',
        ];
    }

    public function authActivityLogs(): HasMany
    {
        return $this->hasMany(AuthActivityLog::class);
    }

    public function sentDepartmentMessages(): HasMany
    {
        return $this->hasMany(DepartmentMessage::class, 'sender_id');
    }

    public function integrationEvents(): HasMany
    {
        return $this->hasMany(IntegrationEvent::class, 'actor_id');
    }

    public function assignedHousekeepingTasks(): HasMany
    {
        return $this->hasMany(HousekeepingTask::class, 'assigned_to');
    }

    public function jwtTokens(): HasMany
    {
        return $this->hasMany(JwtToken::class);
    }

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    public function hasPermissionTo(string $permission): bool
    {
        return app(PermissionService::class)->allows($this, $permission);
    }
}
