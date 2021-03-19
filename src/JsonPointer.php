<?php

declare(strict_types=1);

namespace Slavcodev\JsonPointer;

use function explode;
use function mb_substr;
use function preg_match;
use function strtr;

/**
 * The object implementation of the JSON pointer
 *
 * Rules:
 *
 * - A JSON Pointer is a Unicode string (see [RFC4627], Section 3) containing a sequence of zero or more reference tokens,
 *   each prefixed by a '/' (%x2F) character;
 * - Each reference token contains zero or more Unicode characters: %x00-10FFFF (except special chars `%x2F` and `%x7E`);
 * - Characters `~` and `/` are encoded in reference token into `~0` and `~1` respectively;
 *
 * @see https://tools.ietf.org/html/rfc6901
 *
 * @psalm-immutable
 */
final class JsonPointer
{
    private const SPECIAL_CASES = ['~1' => '/', '~0' => '~'];

    public string $value;

    public bool $anchored = false;

    public array $tokens = [];

    /**
     * @throws InvalidArgumentException if passed string is invalid JSON pointer.
     */
    public function __construct(string $value = '')
    {
        $this->value = $value;

        // If URI fragment representation, strip `#`.
        if (!empty($value) && $value[0] === '#') {
            $value = mb_substr($value, 1);
            $this->anchored = true;
        }

        if (!empty($value)) {
            if ($value[0] !== '/') {
                throw new InvalidArgumentException('Invalid JSON pointer syntax');
            }

            foreach (explode('/', mb_substr($value, 1)) as $key) {
                if (preg_match('/~[^0-1]/', $key)) {
                    throw new InvalidArgumentException('Invalid JSON pointer syntax');
                }

                $this->tokens[] = strtr($key, self::SPECIAL_CASES);
            }
        }
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return $this->value;
    }
}
