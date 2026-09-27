<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown by WorkOrderService when a status update would violate a business rule —
 * currently just "a technician may only update the status of a work order assigned to
 * them." Controllers catch this and turn it into a flashed error message.
 */
class WorkOrderActionException extends Exception
{
}
