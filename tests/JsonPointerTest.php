<?php

declare(strict_types=1);

namespace Slavcodev\JsonPointer\Tests;

use Slavcodev\JsonPointer\InvalidArgumentException;
use Slavcodev\JsonPointer\JsonPointer;
use function array_shift;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class JsonPointerTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideInvalidJsonPointers
     */
    public function validatesValueOnConstruct(string $value): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Invalid JSON pointer syntax'));
        new JsonPointer($value);
    }

    public function provideInvalidJsonPointers(): iterable
    {
        return [
            'must start with token prefix' => ['foo'],
            'must start with `#` representing URI fragment' => ['#foo'],
            'must start with token prefix or `#`' => [' /'],
            'must not contain unescaped `~`' => ['/~foo'],
        ];
    }

    /**
     * @test
     * @dataProvider provideJsonPointersAndUriFragments
     */
    public function isInstantiatedFromString(string $value, array $expectedTokens, bool $anchored): void
    {
        $pointer = new JsonPointer($value);
        self::assertSame($value, $pointer->toString());
        self::assertSame($value, (string) $pointer);
        self::assertSame($expectedTokens, $pointer->tokens);
        self::assertSame($anchored, $pointer->anchored);
    }

    /**
     * @test
     */
    public function isInstantiatedWithDefaultValue(): void
    {
        $pointer = new JsonPointer();
        self::assertSame('', $pointer->toString());
        self::assertSame('', (string) $pointer);
        self::assertSame([], $pointer->tokens);
        self::assertFalse($pointer->anchored);
    }

    /**
     * @psalm-return iterable<string,array{0:string,1:array<int,string>}>
     */
    public function provideJsonPointers(): iterable
    {
        return [
            'basic' => ['/foo', ['foo']],
            'basic (1)' => ['/foo/bar', ['foo', 'bar']],
            'empty' => ['', []],
            'empty token' => ['/', ['']],
            'empty tokens' => ['//', ['', '']],
            'token with space' => ['/ ', [' ']],
            'token with `%`' => ['/f%o', ['f%o']],
            'token with `^`' => ['/f^o', ['f^o']],
            'token with `|`' => ['/f|o', ['f|o']],
            'token with `\\`' => ['/f\\o', ['f\\o']],
            'token with `\'`' => ['/f\'o', ['f\'o']],
            'token with NUL (Unicode U+0000)' => ["/f\0o", ["f\0o"]],
            'token with `"`' => ['/f"o', ['f"o']],
            'token with `/`' => ['/~1foo/bar~1/baz', ['/foo', 'bar/', 'baz']],
            'token with `/` (1)' => ['/f~1o', ['f/o']],
            'token with `~`' => ['/~0foo/bar~0/baz', ['~foo', 'bar~', 'baz']],
            'token with `~` (1)' => ['/f~0o', ['f~o']],
            'numeric token' => ['/foo/0', ['foo', '0']],
        ];
    }

    public function provideJsonPointersAndUriFragments(): iterable
    {
        foreach ($this->provideJsonPointers() as $key => $set) {
            $value = $set[0];
            array_shift($set);
            yield "{$key} - {$value}" => [$value, ...$set, false];
            yield "{$key} - #{$value}" => ["#{$value}", ...$set, true];
        }
    }
}
