<?php

namespace App\Core\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * DomainException — base for all ERP domain exceptions.
 *
 * Usage:
 *   throw new DomainException('Student is already enrolled.', 422);
 *
 *   Or create a typed sub-exception:
 *   throw new NotFoundException('Student', $id);
 */
class DomainException extends Exception
{
    public function __construct(
        string $message = 'A domain error occurred.',
        protected int $statusCode = 422,
        protected array $context = [],
    ) {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function render(Request $request): ?JsonResponse
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'context' => $this->context,
            ], $this->statusCode);
        }

        return null; // Let Laravel render the HTML error page
    }
}
