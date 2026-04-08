<?php

namespace App\Domain\Transaction\Commands;

use App\Domain\Transaction\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MarkOverdueTransactionsCommand extends Command
{
    protected $signature = 'transactions:mark-overdue';

    protected $description = 'Marca transações PENDING vencidas como OVERDUE';

    public function handle(): int
    {
        $count = Transaction::where('status', Transaction::STATUS_PENDING)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->startOfDay())
            ->update(['status' => Transaction::STATUS_OVERDUE]);

        Log::info("Marked {$count} transactions as OVERDUE");
        $this->info("Transações atualizadas: {$count}");

        return self::SUCCESS;
    }
}
