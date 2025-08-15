<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Tests\Statement;

use BackedEnum;
use Jojomi\Dbl\Statement\Field;
use Jojomi\Dbl\Statement\NamedParam;
use Jojomi\Dbl\Statement\Value;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Stringable;

enum TestIntEnum: int
{
    case ONE = 1;
}

enum TestStringEnum: string
{
    case HELLO = 'hello';
}

class TestStringable implements Stringable
{
    public function __toString(): string
    {
        return 'stringable';
    }
}

final class ValueTest extends TestCase
{
    /**
     * @param Value|string|int|bool|Field|NamedParam|Stringable|BackedEnum|null $input
     */
    #[DataProvider('valueProvider')]
    public function testRender(mixed $input, string $expected): void
    {
        self::assertSame($expected, Value::create($input)->render());
    }

    /** @return iterable<string, array{mixed, string}> */
    public static function valueProvider(): iterable
    {
        yield 'string' => ['test', "'test'"];
        yield 'integer' => [42, '42'];
        yield 'boolean true' => [true, '1'];
        yield 'boolean false' => [false, '0'];
        yield 'int enum' => [TestIntEnum::ONE, '1'];
        yield 'string enum' => [TestStringEnum::HELLO, "'hello'"];
        yield 'escaped quote' => ["escaped'quote", "'escaped''quote'"];
        yield 'stringable' => [new TestStringable(), "'stringable'"];
        yield 'null' => [null, 'NULL'];
    }
}
