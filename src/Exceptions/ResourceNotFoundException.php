<?php

namespace CrCms\Repository\Exceptions;

use Throwable;

/**
 * Class ResourceNotFoundException
 *
 * @package CrCms\Repository\Exceptions
 */
class ResourceNotFoundException extends ResourceException
{
    /**
     * ResourceNotFoundException constructor.
     * @param string $message
     */
    public function __construct($message = "resource not found", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}