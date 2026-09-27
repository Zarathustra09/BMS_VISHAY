<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown by MachineTypeService when an update/delete would violate one of the protection
 * rules (a spec field's key changing identity, deleting a type still assigned to a machine).
 * Controllers catch this and turn it into a flashed error message.
 */
class MachineTypeActionException extends Exception {}
