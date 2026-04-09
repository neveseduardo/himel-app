<?php

namespace App\Domain\Period\Exceptions;

use RuntimeException;

class PeriodAlreadyExistsException extends RuntimeException
{
    public static function forMonthYear(int $month, int $year): self
    {
        return new self("O período {$month}/{$year} já existe.");
    }
}
