<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Tests\Statement;

use InvalidArgumentException;
use Jojomi\Dbl\SqlStyle;use Jojomi\Dbl\Statement\Field;
use Jojomi\Dbl\Statement\Table;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{
    public function testCreateWithoutTableOrAlias(): void
    {
        $field = Field::create('name');
        $this->assertInstanceOf(Field::class, $field);
        $this->assertSame('`name`', $field->getDefinition(SqlStyle::MariaDb));
        $this->assertSame('`name`', $field->getAccessor(SqlStyle::MariaDb));
    }

    public function testCreateWithAlias(): void
    {
        $field = Field::create('name', 'n');
        $this->assertSame('`name` AS `n`', $field->getDefinition(SqlStyle::MariaDb));
        $this->assertSame('`n`', $field->getAccessor(SqlStyle::MariaDb));
    }

    public function testCreateWithTableAndAlias(): void
    {
        $table = Table::create('users', 'u');
        $field = Field::create('name', 'n', $table);
        $this->assertSame('`u`.`name` AS `n`', $field->getDefinition(SqlStyle::MariaDb));
        $this->assertSame('`u`.`n`', $field->getAccessor(SqlStyle::MariaDb));
    }

    public function testCreateWithTableWithoutAlias(): void
    {
        $table = Table::create('products');
        $field = Field::create('price', null, $table);
        $this->assertSame('`products`.`price`', $field->getDefinition(SqlStyle::MariaDb));
        $this->assertSame('`products`.`price`', $field->getAccessor(SqlStyle::MariaDb));
    }

    public function testCreateWithRawWithoutAlias(): void
    {
        self::expectException(InvalidArgumentException::class);
        $field = Field::create('COUNT(*)', null, null, true);
        $this->assertSame('COUNT(*)', $field->getDefinition(SqlStyle::MariaDb));
        self::expectException(InvalidArgumentException::class);
        $field->getAccessor(SqlStyle::MariaDb);
    }

    public function testCreateWithRawName(): void
    {
        self::expectException(InvalidArgumentException::class);
        $field = Field::create('COUNT(*)', null, null, true);
        $this->assertSame('COUNT(*)', $field->getDefinition(SqlStyle::MariaDb));
        $field->getAccessor(SqlStyle::MariaDb);
    }

    public function testCreateWithDotInName(): void
    {
        $field = Field::create('table.name');
        $this->assertInstanceOf(Field::class, $field);
        $this->assertSame('`table`.`name`', $field->getDefinition(SqlStyle::MariaDb));
        $this->assertSame('`table`.`name`', $field->getAccessor(SqlStyle::MariaDb));
    }
}