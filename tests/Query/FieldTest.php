<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Tests\Query;

use InvalidArgumentException;
use Jojomi\Dbl\Statement\Field;
use Jojomi\Dbl\Statement\Table;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{
    public function testCreateWithoutTableOrAlias(): void
    {
        $field = Field::create('name');
        $this->assertInstanceOf(Field::class, $field);
        $this->assertSame('`name`', $field->getDefinition());
        $this->assertSame('`name`', $field->getAccessor());
    }

    public function testCreateWithAlias(): void
    {
        $field = Field::create('name', 'n');
        $this->assertSame('`name` AS \'n\'', $field->getDefinition());
        $this->assertSame('`n`', $field->getAccessor());
    }

    public function testCreateWithTableAndAlias(): void
    {
        $table = Table::create('users', 'u');
        $field = Field::create('name', 'n', $table);
        $this->assertSame('`u`.`name` AS \'n\'', $field->getDefinition());
        $this->assertSame('`u`.`n`', $field->getAccessor());
    }

    public function testCreateWithTableWithoutAlias(): void
    {
        $table = Table::create('products');
        $field = Field::create('price', null, $table);
        $this->assertSame('`products`.`price`', $field->getDefinition());
        $this->assertSame('`products`.`price`', $field->getAccessor());
    }

    public function testCreateWithRawWithoutAlias(): void
    {
        self::expectException(InvalidArgumentException::class);
        $field = Field::create('COUNT(*)', null, null, true);
        $this->assertSame('COUNT(*)', $field->getDefinition());
        self::expectException(InvalidArgumentException::class);
        $field->getAccessor();
    }

    public function testCreateWithRawName(): void
    {
        self::expectException(InvalidArgumentException::class);
        $field = Field::create('COUNT(*)', null, null, true);
        $this->assertSame('COUNT(*)', $field->getDefinition());
        $field->getAccessor();
    }
}