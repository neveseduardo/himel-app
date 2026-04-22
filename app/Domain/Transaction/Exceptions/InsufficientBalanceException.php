<?php

namespace App\Domain\Transaction\Exceptions;

use App\Domain\Account\Models\Account;

class InsufficientBalanceException extends \RuntimeException
{
    public function __construct(
        public readonly Account $account,
        public readonly float $requiredAmount,
    ) {
        $formatted = number_format($requiredAmount, 2, ',', '.');
        $available = number_format($account->balance, 2, ',', '.');
        parent::__construct(
            "Saldo insuficiente na conta '{$account->name}'. "
            ."Necessário: R$ {$formatted}. Disponível: R$ {$available}. "
            .'Considere realizar uma transferência entre contas para disponibilizar o saldo.'
        );
    }
}
