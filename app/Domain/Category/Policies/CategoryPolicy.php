<?php

namespace App\Domain\Category\Policies;

use App\Domain\Category\Models\Category;
use App\Domain\User\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Category $category): bool
    {
        return $category->user_uid === $user->uid;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Category $category): bool
    {
        return $category->user_uid === $user->uid;
    }

    public function delete(User $user, Category $category): bool
    {
        return $category->user_uid === $user->uid;
    }

    public function restore(User $user, Category $category): bool
    {
        return $category->user_uid === $user->uid;
    }

    public function forceDelete(User $user, Category $category): bool
    {
        return $category->user_uid === $user->uid;
    }
}
