<?php

namespace App\Domain\CreditCardCharge\Controllers;

use App\Domain\CreditCard\Contracts\CreditCardServiceInterface;
use App\Domain\CreditCardCharge\Contracts\CreditCardChargeServiceInterface;
use App\Domain\CreditCardCharge\Requests\StoreCreditCardChargeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class CreditCardChargePageController
{
    public function __construct(
        private readonly CreditCardChargeServiceInterface $creditCardChargeService,
        private readonly CreditCardServiceInterface $creditCardService,
    ) {}

    public function index(Request $request): Response
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'card_uid', 'search']);
        $result = $this->creditCardChargeService->getAllWithFilters($userUid, $filters);

        return Inertia::render('finance/credit-card-charges/Index', [
            'charges' => $result['data'],
            'meta' => $result['meta'],
            'filters' => $filters,
            'creditCards' => Inertia::optional(fn () => $this->creditCardService->getAll($userUid)),
        ]);
    }

    public function create(Request $request): Response
    {
        $userUid = $request->user()->uid;

        return Inertia::render('finance/credit-card-charges/Create', [
            'creditCards' => $this->creditCardService->getAll($userUid),
        ]);
    }

    public function store(StoreCreditCardChargeRequest $request): RedirectResponse
    {
        try {
            $this->creditCardChargeService->create($request->validated(), $request->user()->uid);

            return redirect()->route('finance.credit-card-charges.index')->with('success', 'Compra registrada com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Failed to create credit card charge', ['error' => $e->getMessage()]);

            return back()->with('error', 'Erro ao registrar compra.');
        }
    }

    public function show(Request $request, string $uid): Response
    {
        $charge = $this->creditCardChargeService->getByUid($uid, $request->user()->uid);
        abort_unless($charge, 404);

        return Inertia::render('finance/credit-card-charges/Show', ['charge' => $charge]);
    }
}
