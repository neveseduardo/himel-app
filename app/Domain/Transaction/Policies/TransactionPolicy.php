<?php

namespace App\Domain\Transaction\Policies;

use App\Domain\Transaction\Models\Transaction;
use App\Domain\User\Models\User;

class TransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Transaction $transaction): bool
    {
        return $transaction->user_uid === $user->uid;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Transaction $transaction): bool
    {
        return $transaction->user_uid === $user->uid;
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return $transaction->user_uid === $user->uid;
    }

    public function restore(User $user, Transaction $transaction): bool
    {
        return $transaction->user_uid === $user->uid;
    }

    public function forceDelete(User $user, Transaction $transaction): bool
    {
        return $transaction->user_uid === $user->uid;
    }
}
