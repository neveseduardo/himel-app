<?php

namespace App\Domain\Category\Listeners;

use App\Domain\Category\Models\Category;
use Illuminate\Auth\Events\Login;

class CreateDefaultCategoriesListener
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (Category::where('user_uid', $user->uid)->exists()) {
            return;
        }

        $categories = [
            ['name' => 'Alimentação', 'direction' => Category::DIRECTION_OUTFLOW],
            ['name' => 'Moradia', 'direction' => Category::DIRECTION_OUTFLOW],
            ['name' => 'Transporte', 'direction' => Category::DIRECTION_OUTFLOW],
            ['name' => 'Saúde', 'direction' => Category::DIRECTION_OUTFLOW],
            ['name' => 'Educação', 'direction' => Category::DIRECTION_OUTFLOW],
            ['name' => 'Lazer', 'direction' => Category::DIRECTION_OUTFLOW],
            ['name' => 'Vestuário', 'direction' => Category::DIRECTION_OUTFLOW],
            ['name' => 'Outros', 'direction' => Category::DIRECTION_OUTFLOW],
            ['name' => 'Salário', 'direction' => Category::DIRECTION_INFLOW],
            ['name' => 'Freelance', 'direction' => Category::DIRECTION_INFLOW],
            ['name' => 'Investimentos', 'direction' => Category::DIRECTION_INFLOW],
            ['name' => 'Outros', 'direction' => Category::DIRECTION_INFLOW],
        ];

        foreach ($categories as $category) {
            Category::create([
                'user_uid' => $user->uid,
                'name' => $category['name'],
                'direction' => $category['direction'],
            ]);
        }
    }
}
