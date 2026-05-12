<?php

namespace App\Core\Exceptions;

/**
 * NotFoundException — thrown when a resource cannot be found.
 *
 * Usage:
 *   throw new NotFoundException('Student', $id);
 */
class NotFoundException extends DomainException
{
    public function __construct(string $resource = 'Resource', int|string $id = '')
    {
        $message = $id
            ? "{$resource} with ID [{$id}] was not found."
            : "{$resource} not found.";

        parent::__construct($message, 404);
    }
}
