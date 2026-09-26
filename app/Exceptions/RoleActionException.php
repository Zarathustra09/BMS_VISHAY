<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown by RoleService when a role update/delete would violate one of the
 * protection rules (admin role, roles still holding users, self-lockout).
 * Controllers catch this and turn it into a flashed error message.
 */
class RoleActionException extends Exception
{
}
