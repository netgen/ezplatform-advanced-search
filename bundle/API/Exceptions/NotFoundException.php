<?php

declare(strict_types=1);

namespace  Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Exceptions;

use Exception;

class NotFoundException extends Exception
{
    /**
     * Generates: Could not find '{$what}' with identifier '{$identifier}'.
     *
     * @param string $what
     * @param mixed $identifier
     * @param \Exception|null $previous
     */
    public function __construct(string $what, $identifier, Exception $previous = null)
    {
        $identifier = \is_string($identifier) ? $identifier : \var_export($identifier, true);
        $message = "Could not find '{$what}' with identifier '{$identifier}'";

        parent::__construct($message, 404, $previous);
    }
}
