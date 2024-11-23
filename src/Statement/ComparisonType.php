<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

/**
 * ComparisonType.
 */
enum ComparisonType: string
{
    case equal = '=';
    case unequal = '<>';
    case lessThan = '<';
    case lessThanOrEqual = '<=';
    case greaterThan = '>';
    case greaterThanOrEqual = '>=';
    case null = 'IS NULL';
    case notNull = 'IS NOT NULL';
}