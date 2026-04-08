<?php

namespace App\Domain\Period\Controllers;

use App\Domain\Period\Contracts\PeriodServiceInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PeriodPageController
{
    public function __construct(
        private readonly PeriodServiceInterface $periodService
    ) {}

    public function index(Request $request): Response
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'month', 'year']);
        $result = $this->periodService->getAllWithFilters($userUid, $filters);

        return Inertia::render('finance/periods/Index', [
            'periods' => $result['data'],
            'meta' => $result['meta'],
            'filters' => $filters,
        ]);
    }
}
