<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'department_id', 'status', 'last_login_at'])]
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
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function hasRole(string $role): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains('name', $role);
        }

        return $this->roles()->where('name', $role)->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->whereIn('name', $roles)->isNotEmpty();
        }

        return $this->roles()->whereIn('name', $roles)->exists();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles()
            ->whereHas('permissions', fn ($query) => $query->where('name', $permission))
            ->exists();
    }

    public function canManageDocuments(): bool
    {
        return $this->hasAnyRole(['document-admin', 'super-admin']);
    }

    public function canManageUsers(): bool
    {
        return $this->hasAnyRole(['document-admin', 'super-admin']);
    }

    public function canManageSettings(): bool
    {
        return $this->hasRole('super-admin');
    }

    public function canReadGovernance(): bool
    {
        return $this->hasAnyRole(['document-admin', 'super-admin', 'auditor']);
    }

    public function canViewAllPublishedDocuments(): bool
    {
        return $this->hasAnyRole(['bod', 'document-admin', 'super-admin', 'auditor']);
    }

    public function roleScopedDepartmentIds(): array
    {
        $roleIds = $this->relationLoaded('roles')
            ? $this->roles->pluck('id')->all()
            : $this->roles()->pluck('roles.id')->all();

        if ($roleIds === []) {
            return [];
        }

        return Department::query()
            ->where('active', true)
            ->whereHas('roles', fn ($query) => $query->whereIn('roles.id', $roleIds))
            ->pluck('departments.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function accessibleDepartmentIds(): array
    {
        if ($this->canViewAllPublishedDocuments()) {
            return Department::query()
                ->where('active', true)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $departmentIds = $this->roleScopedDepartmentIds();

        if ($departmentIds === [] && $this->department_id) {
            $departmentIds[] = (int) $this->department_id;
        }

        return array_values(array_unique($departmentIds));
    }
}
