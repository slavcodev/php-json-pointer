<?php

declare(strict_types=1);

namespace Slavcodev\JsonPointer;

use InvalidArgumentException as PhpInvalidArgumentException;
use Throwable;

final class InvalidArgumentException extends PhpInvalidArgumentException implements JsonPointerException
{
    public function __construct(string $message, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
