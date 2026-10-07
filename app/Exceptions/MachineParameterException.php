<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown by MachineParameterService::sync() when a submitted parameter list would break one of
 * the protection rules (a key clashing with another parameter, or a row id that doesn't belong
 * to the machine). Controllers turn it into a validation error on the `parameters` field.
 */
class MachineParameterException extends Exception {}
