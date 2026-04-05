<?php

namespace App\Domain\Account\Policies;

use App\Domain\Account\Models\Account;
use App\Domain\User\Models\User;

class AccountPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Account $account): bool
    {
        return $account->user_uid === $user->uid;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Account $account): bool
    {
        return $account->user_uid === $user->uid;
    }

    public function delete(User $user, Account $account): bool
    {
        return $account->user_uid === $user->uid;
    }

    public function restore(User $user, Account $account): bool
    {
        return $account->user_uid === $user->uid;
    }

    public function forceDelete(User $user, Account $account): bool
    {
        return $account->user_uid === $user->uid;
    }
}
