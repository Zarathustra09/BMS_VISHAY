<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    use HasRoles {
        hasPermissionTo as private hasOwnPermissionTo;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The groups this user belongs to. A group holds roles/permissions that every member inherits
     * (see hasPermissionTo()) and can be a machine's assignment group for alert emails.
     *
     * @return BelongsToMany<Group, $this>
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class)->withTimestamps();
    }

    /**
     * Spatie's check, plus anything the user's groups grant.
     *
     * Spatie's Gate::before callback ends up here (via checkPermissionTo), so overriding this one
     * method makes @can, $user->can() and the `permission:` middleware all honour group
     * membership. It does NOT change hasRole()/getRoleNames()/User::role(), which still report
     * only the roles assigned to the user directly.
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if ($this->hasOwnPermissionTo($permission, $guardName)) {
            return true;
        }

        return $this->loadMissing(['groups.roles', 'groups.permissions'])
            ->groups
            ->contains(fn (Group $group) => $group->hasPermissionTo($permission, $guardName));
    }

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
}
