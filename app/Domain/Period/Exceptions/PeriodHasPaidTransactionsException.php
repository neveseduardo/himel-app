<?php

namespace App\Domain\Period\Exceptions;

use RuntimeException;

class PeriodHasPaidTransactionsException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Períodos com transações pagas não podem ser excluídos.');
    }
}
