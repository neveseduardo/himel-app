<?php

namespace Tests\Feature;

use App\Domain\Account\Models\Account;
use App\Domain\Category\Models\Category;
use App\Domain\Period\Models\Period;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriodCreationAndDeletionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Account $account;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->account = Account::create([
            'user_uid' => $this->user->uid,
            'name' => 'Main Account',
            'type' => Account::TYPE_CHECKING,
            'balance' => 5000,
        ]);
        $this->category = Category::create([
            'user_uid' => $this->user->uid,
            'name' => 'Moradia',
            'direction' => Category::DIRECTION_OUTFLOW,
        ]);
    }

    // 16.1 — Test period creation with valid data (HTTP 201)

    public function test_creates_period_with_valid_data_returns_201(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/periods', [
                'month' => 6,
                'year' => 2025,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.month', 6)
            ->assertJsonPath('data.year', 2025);

        $this->assertDatabaseHas('financial_periods', [
            'user_uid' => $this->user->uid,
            'month' => 6,
            'year' => 2025,
        ]);
    }

    // 16.2 — Test duplicate period rejection (HTTP 409)

    public function test_rejects_duplicate_period_with_409(): void
    {
        Period::create([
            'user_uid' => $this->user->uid,
            'month' => 6,
            'year' => 2025,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/periods', [
                'month' => 6,
                'year' => 2025,
            ]);

        $response->assertStatus(409)
            ->assertJsonStructure(['error']);
    }

    // 16.3 — Test validation of invalid month/year fields (HTTP 422)

    public function test_rejects_month_below_range_with_422(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/periods', [
                'month' => 0,
                'year' => 2025,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['month']);
    }

    public function test_rejects_month_above_range_with_422(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/periods', [
                'month' => 13,
                'year' => 2025,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['month']);
    }

    public function test_rejects_missing_month_with_422(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/periods', [
                'year' => 2025,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['month']);
    }

    public function test_rejects_missing_year_with_422(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/periods', [
                'month' => 6,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['year']);
    }

    public function test_rejects_year_below_range_with_422(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/periods', [
                'month' => 6,
                'year' => 1999,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['year']);
    }

    // 16.4 — Test deletion of period without transactions

    public function test_deletes_period_without_transactions(): void
    {
        $period = Period::create([
            'user_uid' => $this->user->uid,
            'month' => 6,
            'year' => 2025,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/periods/{$period->uid}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('financial_periods', ['uid' => $period->uid]);
    }

    // 16.5 — Test rejection of deletion with PAID transactions

    public function test_rejects_deletion_when_period_has_paid_transactions(): void
    {
        $period = Period::create([
            'user_uid' => $this->user->uid,
            'month' => 6,
            'year' => 2025,
        ]);

        Transaction::create([
            'user_uid' => $this->user->uid,
            'account_uid' => $this->account->uid,
            'category_uid' => $this->category->uid,
            'amount' => 100.00,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => Transaction::STATUS_PAID,
            'source' => Transaction::SOURCE_MANUAL,
            'occurred_at' => now(),
            'period_uid' => $period->uid,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/periods/{$period->uid}");

        $response->assertStatus(422)
            ->assertJsonStructure(['error']);

        $this->assertDatabaseHas('financial_periods', ['uid' => $period->uid]);
    }

    // 16.6 — Test deletion with unlinking of PENDING/OVERDUE transactions

    public function test_deletes_period_and_unlinks_pending_transactions(): void
    {
        $period = Period::create([
            'user_uid' => $this->user->uid,
            'month' => 6,
            'year' => 2025,
        ]);

        $pending = Transaction::create([
            'user_uid' => $this->user->uid,
            'account_uid' => $this->account->uid,
            'category_uid' => $this->category->uid,
            'amount' => 200.00,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => Transaction::STATUS_PENDING,
            'source' => Transaction::SOURCE_FIXED,
            'occurred_at' => now(),
            'period_uid' => $period->uid,
        ]);

        $overdue = Transaction::create([
            'user_uid' => $this->user->uid,
            'account_uid' => $this->account->uid,
            'category_uid' => $this->category->uid,
            'amount' => 150.00,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => Transaction::STATUS_OVERDUE,
            'source' => Transaction::SOURCE_MANUAL,
            'occurred_at' => now(),
            'period_uid' => $period->uid,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/periods/{$period->uid}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('financial_periods', ['uid' => $period->uid]);

        $this->assertDatabaseHas('financial_transactions', [
            'uid' => $pending->uid,
            'period_uid' => null,
        ]);
        $this->assertDatabaseHas('financial_transactions', [
            'uid' => $overdue->uid,
            'period_uid' => null,
        ]);
    }
}
