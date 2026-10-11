<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;

/**
 * A set of users that share roles/permissions and, when assigned to a machine, receive that
 * machine's alert emails. Roles and permissions are held exactly like a User holds them (Spatie's
 * HasRoles); User::hasPermissionTo() also checks the user's groups, so a member inherits them.
 */
class Group extends Model
{
    /** @use HasFactory<\Database\Factories\GroupFactory> */
    use HasFactory, HasRoles;

    /**
     * Spatie resolves a model's guard from the auth provider that uses its class; Group is not an
     * authenticatable model, so without this assignRole()/givePermissionTo() throw GuardDoesNotMatch.
     */
    protected $guard_name = 'web';

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Machines that use this group as their assignment group.
     *
     * @return HasMany<Machine, $this>
     */
    public function machines(): HasMany
    {
        return $this->hasMany(Machine::class, 'assignment_group_id');
    }
}
