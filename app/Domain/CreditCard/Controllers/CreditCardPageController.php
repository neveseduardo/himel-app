<?php

namespace App\Domain\CreditCard\Controllers;

use App\Domain\CreditCard\Contracts\CreditCardServiceInterface;
use App\Domain\CreditCard\Requests\StoreCreditCardRequest;
use App\Domain\CreditCard\Requests\UpdateCreditCardRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class CreditCardPageController
{
    public function __construct(
        private readonly CreditCardServiceInterface $creditCardService
    ) {}

    public function index(Request $request): Response
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'card_type', 'search']);
        $result = $this->creditCardService->getAllWithFilters($userUid, $filters);

        return Inertia::render('credit-cards/Index', [
            'creditCards' => $result['data'],
            'meta' => $result['meta'],
            'filters' => $filters,
        ]);
    }

    public function store(StoreCreditCardRequest $request): RedirectResponse
    {
        try {
            $this->creditCardService->create($request->validated(), $request->user()->uid);

            return redirect()->route('credit-cards.index')->with('success', 'Cartão criado com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Failed to create credit card', ['error' => $e->getMessage()]);

            return back()->with('error', 'Erro ao criar cartão.');
        }
    }

    public function update(UpdateCreditCardRequest $request, string $uid): RedirectResponse
    {
        try {
            $this->creditCardService->update($uid, $request->validated(), $request->user()->uid);

            return redirect()->route('credit-cards.index')->with('success', 'Cartão atualizado com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Failed to update credit card', ['error' => $e->getMessage()]);

            return back()->with('error', 'Erro ao atualizar cartão.');
        }
    }

    public function destroy(Request $request, string $uid): RedirectResponse
    {
        try {
            $this->creditCardService->delete($uid, $request->user()->uid);

            return redirect()->route('credit-cards.index')->with('success', 'Cartão excluído com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Failed to delete credit card', ['error' => $e->getMessage()]);

            return back()->with('error', 'Erro ao excluir cartão.');
        }
    }
}
