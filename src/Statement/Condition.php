<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use Jojomi\Dbl\SqlStyle;/**
 * Condition.
 */
interface Condition
{
    public function render(SqlStyle $sqlStyle): string;

    /**
     * Noop iff $table is already set.
     */
    public function withTable(Table $table): static;

    public function requiresBrackets(): bool;
}