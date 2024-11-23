<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

/**
 * StatementType.
 */
enum StatementType
{
    case Select;
    case Insert;
    case Update;
    case Delete;
}