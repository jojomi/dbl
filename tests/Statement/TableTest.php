<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Tests\Statement;

use Jojomi\Dbl\SqlStyle;use Jojomi\Dbl\Statement\Table;
use PHPUnit\Framework\TestCase;

class TableTest extends TestCase
{
    public function testCreateWithAlias(): void
    {
        $table = Table::create('users', 'u');
        $this->assertInstanceOf(Table::class, $table);
        $this->assertSame('`users` `u`', $table->getDefinition(SqlStyle::MariaDb));
        $this->assertSame('`u`', $table->getPrefix(SqlStyle::MariaDb));
    }

    public function testCreateWithoutAlias(): void
    {
        $table = Table::create('products');
        $this->assertInstanceOf(Table::class, $table);
        $this->assertSame('`products`', $table->getDefinition(SqlStyle::MariaDb));
        $this->assertSame('`products`', $table->getPrefix(SqlStyle::MariaDb));
    }
}
