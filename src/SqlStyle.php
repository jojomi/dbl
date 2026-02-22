<?php

declare(strict_types=1);

namespace Jojomi\Dbl;

enum SqlStyle {
    case MariaDb;
    case Postgres;
}