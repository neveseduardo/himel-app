<?php

namespace App\Domain\CreditCardCharge\Controllers;

use App\Domain\CreditCard\Contracts\CreditCardServiceInterface;
use App\Domain\CreditCardCharge\Contracts\CreditCardChargeServiceInterface;
use App\Domain\CreditCardCharge\Requests\StoreCreditCardChargeRequest;
use App\Domain\CreditCardCharge\Requests\UpdateCreditCardChargeRequest;
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

        return Inertia::render('credit-card-charges/Index', [
            'charges' => $result['data'],
            'meta' => $result['meta'],
            'filters' => $filters,
            'creditCards' => $this->creditCardService->getAll($userUid),
        ]);
    }

    public function store(StoreCreditCardChargeRequest $request): RedirectResponse
    {
        try {
            $this->creditCardChargeService->create($request->validated(), $request->user()->uid);

            return redirect()->route('credit-card-charges.index')->with('success', 'Compra registrada com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Failed to create credit card charge', ['error' => $e->getMessage()]);

            return back()->with('error', 'Erro ao registrar compra.');
        }
    }

    public function update(UpdateCreditCardChargeRequest $request, string $uid): RedirectResponse
    {
        try {
            $this->creditCardChargeService->update($uid, $request->validated(), $request->user()->uid);

            return redirect()->route('credit-card-charges.index')->with('success', 'Compra atualizada com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Failed to update credit card charge', ['error' => $e->getMessage()]);

            return back()->with('error', 'Erro ao atualizar compra.');
        }
    }

    public function destroy(Request $request, string $uid): RedirectResponse
    {
        try {
            $this->creditCardChargeService->delete($uid, $request->user()->uid);

            return redirect()->route('credit-card-charges.index')->with('success', 'Compra excluída com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Failed to delete credit card charge', ['error' => $e->getMessage()]);

            return back()->with('error', 'Erro ao excluir compra.');
        }
    }
}
