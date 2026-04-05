<?php

namespace App\Domain\Period\Policies;

use App\Domain\Period\Models\Period;
use App\Domain\User\Models\User;

class PeriodPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Period $period): bool
    {
        return $period->user_uid === $user->uid;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Period $period): bool
    {
        return $period->user_uid === $user->uid;
    }

    public function delete(User $user, Period $period): bool
    {
        return $period->user_uid === $user->uid;
    }

    public function restore(User $user, Period $period): bool
    {
        return $period->user_uid === $user->uid;
    }

    public function forceDelete(User $user, Period $period): bool
    {
        return $period->user_uid === $user->uid;
    }
}
