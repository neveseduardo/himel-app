<?php

namespace App\Domain\Transfer\Policies;

use App\Domain\Transfer\Models\Transfer;
use App\Domain\User\Models\User;

class TransferPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Transfer $transfer): bool
    {
        return $transfer->user_uid === $user->uid;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Transfer $transfer): bool
    {
        return $transfer->user_uid === $user->uid;
    }

    public function delete(User $user, Transfer $transfer): bool
    {
        return $transfer->user_uid === $user->uid;
    }

    public function restore(User $user, Transfer $transfer): bool
    {
        return $transfer->user_uid === $user->uid;
    }

    public function forceDelete(User $user, Transfer $transfer): bool
    {
        return $transfer->user_uid === $user->uid;
    }
}
