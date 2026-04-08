<?php

namespace Tests\Feature\Domain\Category;

use App\Domain\Category\Listeners\CreateDefaultCategoriesListener;
use App\Domain\Category\Models\Category;
use App\Domain\User\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateDefaultCategoriesListenerTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_default_categories_on_first_login(): void
    {
        $user = User::factory()->create();

        $listener = new CreateDefaultCategoriesListener;
        $listener->handle(new Login('web', $user, false));

        $this->assertDatabaseCount('financial_categories', 12);

        $outflowCategories = Category::where('user_uid', $user->uid)
            ->where('direction', Category::DIRECTION_OUTFLOW)
            ->pluck('name')
            ->toArray();

        $this->assertCount(8, $outflowCategories);
        $this->assertContains('Alimentação', $outflowCategories);
        $this->assertContains('Moradia', $outflowCategories);
        $this->assertContains('Transporte', $outflowCategories);
        $this->assertContains('Saúde', $outflowCategories);
        $this->assertContains('Educação', $outflowCategories);
        $this->assertContains('Lazer', $outflowCategories);
        $this->assertContains('Vestuário', $outflowCategories);
        $this->assertContains('Outros', $outflowCategories);

        $inflowCategories = Category::where('user_uid', $user->uid)
            ->where('direction', Category::DIRECTION_INFLOW)
            ->pluck('name')
            ->toArray();

        $this->assertCount(4, $inflowCategories);
        $this->assertContains('Salário', $inflowCategories);
        $this->assertContains('Freelance', $inflowCategories);
        $this->assertContains('Investimentos', $inflowCategories);
        $this->assertContains('Outros', $inflowCategories);
    }

    public function test_skips_creation_when_user_already_has_categories(): void
    {
        $user = User::factory()->create();

        Category::create([
            'user_uid' => $user->uid,
            'name' => 'Existing Category',
            'direction' => Category::DIRECTION_OUTFLOW,
        ]);

        $listener = new CreateDefaultCategoriesListener;
        $listener->handle(new Login('web', $user, false));

        $this->assertDatabaseCount('financial_categories', 1);
    }

    public function test_all_categories_are_associated_with_the_user(): void
    {
        $user = User::factory()->create();

        $listener = new CreateDefaultCategoriesListener;
        $listener->handle(new Login('web', $user, false));

        $categories = Category::where('user_uid', $user->uid)->count();
        $this->assertEquals(12, $categories);

        $otherCategories = Category::where('user_uid', '!=', $user->uid)->count();
        $this->assertEquals(0, $otherCategories);
    }
}
