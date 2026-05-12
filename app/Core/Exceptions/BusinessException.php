<?php

namespace App\Core\Exceptions;

/**
 * BusinessException — thrown when a business rule is violated.
 *
 * Usage:
 *   throw new BusinessException('Student has already been promoted this session.');
 */
class BusinessException extends DomainException
{
    public function __construct(string $message, array $context = [])
    {
        parent::__construct($message, 422, $context);
    }
}
