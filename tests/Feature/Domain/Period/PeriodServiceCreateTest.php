<?php

namespace Tests\Feature\Domain\Period;

use App\Domain\Period\Exceptions\PeriodAlreadyExistsException;
use App\Domain\Period\Models\Period;
use App\Domain\Period\Services\PeriodService;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriodServiceCreateTest extends TestCase
{
    use RefreshDatabase;

    private PeriodService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PeriodService;
    }

    public function test_create_period_successfully(): void
    {
        $user = User::factory()->create();

        $period = $this->service->create($user->uid, 6, 2025);

        $this->assertInstanceOf(Period::class, $period);
        $this->assertEquals($user->uid, $period->user_uid);
        $this->assertEquals(6, $period->month);
        $this->assertEquals(2025, $period->year);
        $this->assertDatabaseHas('financial_periods', [
            'user_uid' => $user->uid,
            'month' => 6,
            'year' => 2025,
        ]);
    }

    public function test_create_throws_exception_for_duplicate_period(): void
    {
        $user = User::factory()->create();

        $this->service->create($user->uid, 6, 2025);

        $this->expectException(PeriodAlreadyExistsException::class);
        $this->expectExceptionMessage('O período 6/2025 já existe.');

        $this->service->create($user->uid, 6, 2025);
    }

    public function test_create_allows_same_month_year_for_different_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $period1 = $this->service->create($user1->uid, 6, 2025);
        $period2 = $this->service->create($user2->uid, 6, 2025);

        $this->assertNotEquals($period1->uid, $period2->uid);
        $this->assertDatabaseCount('financial_periods', 2);
    }

    public function test_create_allows_different_month_for_same_user(): void
    {
        $user = User::factory()->create();

        $period1 = $this->service->create($user->uid, 5, 2025);
        $period2 = $this->service->create($user->uid, 6, 2025);

        $this->assertNotEquals($period1->uid, $period2->uid);
        $this->assertEquals(5, $period1->month);
        $this->assertEquals(6, $period2->month);
    }
}
