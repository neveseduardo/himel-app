<?php

namespace Tests\Feature\Domain\CreditCardCharge;

use App\Domain\CreditCard\Models\CreditCard;
use App\Domain\CreditCardCharge\Models\CreditCardCharge;
use App\Domain\CreditCardCharge\Services\CreditCardChargeService;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditCardChargeServiceTest extends TestCase
{
    use RefreshDatabase;

    private CreditCardChargeService $service;

    private User $user;

    private CreditCard $card1;

    private CreditCard $card2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CreditCardChargeService;
        $this->user = User::factory()->create();

        $this->card1 = CreditCard::create([
            'user_uid' => $this->user->uid,
            'name' => 'Nubank',
            'card_type' => CreditCard::CARD_TYPE_PHYSICAL,
            'due_day' => 15,
            'closing_day' => 5,
            'last_four_digits' => '1234',
        ]);

        $this->card2 = CreditCard::create([
            'user_uid' => $this->user->uid,
            'name' => 'Inter',
            'card_type' => CreditCard::CARD_TYPE_VIRTUAL,
            'due_day' => 20,
            'closing_day' => 10,
            'last_four_digits' => '5678',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createCharge(CreditCard $card, array $overrides = []): CreditCardCharge
    {
        return CreditCardCharge::create(array_merge([
            'credit_card_uid' => $card->uid,
            'description' => 'Compra teste',
            'amount' => 500.00,
            'total_installments' => 1,
            'purchase_date' => '2026-04-10',
        ], $overrides));
    }

    // ── getAllWithFilters ─────────────────────────────────────────────

    public function test_get_all_with_filters_returns_paginated_results(): void
    {
        $this->createCharge($this->card1);
        $this->createCharge($this->card2);

        $result = $this->service->getAllWithFilters($this->user->uid);

        $this->assertCount(2, $result['data']);
        $this->assertEquals(2, $result['meta']['total']);
        $this->assertEquals(10, $result['meta']['per_page']);
    }

    public function test_get_all_with_filters_filters_by_card_uid(): void
    {
        $this->createCharge($this->card1, ['description' => 'Compra Nubank']);
        $this->createCharge($this->card2, ['description' => 'Compra Inter']);
        $this->createCharge($this->card1, ['description' => 'Outra Nubank']);

        $result = $this->service->getAllWithFilters($this->user->uid, [
            'card_uid' => $this->card1->uid,
        ]);

        $this->assertCount(2, $result['data']);
        $this->assertEquals(2, $result['meta']['total']);
    }

    public function test_get_all_with_filters_filters_by_search(): void
    {
        $this->createCharge($this->card1, ['description' => 'Notebook Dell']);
        $this->createCharge($this->card1, ['description' => 'Fone Bluetooth']);

        $result = $this->service->getAllWithFilters($this->user->uid, [
            'search' => 'Notebook',
        ]);

        $this->assertCount(1, $result['data']);
    }

    public function test_get_all_with_filters_combines_card_uid_and_search(): void
    {
        $this->createCharge($this->card1, ['description' => 'Amazon Nubank']);
        $this->createCharge($this->card2, ['description' => 'Amazon Inter']);
        $this->createCharge($this->card1, ['description' => 'Shopee Nubank']);

        $result = $this->service->getAllWithFilters($this->user->uid, [
            'card_uid' => $this->card1->uid,
            'search' => 'Amazon',
        ]);

        $this->assertCount(1, $result['data']);
    }

    public function test_get_all_with_filters_excludes_other_user_charges(): void
    {
        $otherUser = User::factory()->create();
        $otherCard = CreditCard::create([
            'user_uid' => $otherUser->uid,
            'name' => 'Outro',
            'card_type' => CreditCard::CARD_TYPE_PHYSICAL,
            'due_day' => 10,
            'closing_day' => 1,
            'last_four_digits' => '0000',
        ]);

        $this->createCharge($this->card1);
        $this->createCharge($otherCard);

        $result = $this->service->getAllWithFilters($this->user->uid);

        $this->assertCount(1, $result['data']);
    }

    public function test_get_all_with_filters_supports_pagination(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->createCharge($this->card1, ['description' => "Compra {$i}"]);
        }

        $page1 = $this->service->getAllWithFilters($this->user->uid, ['per_page' => 2, 'page' => 1]);
        $page2 = $this->service->getAllWithFilters($this->user->uid, ['per_page' => 2, 'page' => 2]);

        $this->assertCount(2, $page1['data']);
        $this->assertCount(2, $page2['data']);
        $this->assertEquals(5, $page1['meta']['total']);
        $this->assertEquals(3, $page1['meta']['last_page']);
    }

    public function test_get_all_with_filters_caps_per_page_at_100(): void
    {
        $result = $this->service->getAllWithFilters($this->user->uid, ['per_page' => 500]);

        $this->assertEquals(100, $result['meta']['per_page']);
    }

    public function test_get_all_with_filters_returns_empty_for_nonexistent_card_uid(): void
    {
        $this->createCharge($this->card1);

        $result = $this->service->getAllWithFilters($this->user->uid, [
            'card_uid' => 'nonexistent-uid',
        ]);

        $this->assertCount(0, $result['data']);
        $this->assertEquals(0, $result['meta']['total']);
    }
}
