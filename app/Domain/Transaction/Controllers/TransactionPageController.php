<?php

namespace App\Domain\Transaction\Controllers;

use App\Domain\Account\Contracts\AccountServiceInterface;
use App\Domain\Category\Contracts\CategoryServiceInterface;
use App\Domain\Transaction\Contracts\TransactionServiceInterface;
use App\Domain\Transaction\Requests\StoreTransactionRequest;
use App\Domain\Transaction\Requests\UpdateTransactionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class TransactionPageController
{
    public function __construct(
        private readonly TransactionServiceInterface $transactionService,
        private readonly AccountServiceInterface $accountService,
        private readonly CategoryServiceInterface $categoryService,
    ) {}

    public function index(Request $request): Response
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'status', 'direction', 'source', 'account_uid', 'category_uid', 'date_from', 'date_to', 'search']);
        $result = $this->transactionService->getAllWithFilters($userUid, $filters);

        return Inertia::render('transactions/Index', [
            'transactions' => $result['data'],
            'meta' => $result['meta'],
            'filters' => $filters,
            'accounts' => fn () => $this->accountService->getAll($userUid),
            'categories' => fn () => $this->categoryService->getAll($userUid),
        ]);
    }

    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        try {
            $this->transactionService->create($request->validated(), $request->user()->uid);

            return redirect()->route('transactions.index')->with('success', 'Transação criada com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Failed to create transaction', ['error' => $e->getMessage()]);

            return back()->with('error', 'Erro ao criar transação.');
        }
    }

    public function update(UpdateTransactionRequest $request, string $uid): RedirectResponse
    {
        try {
            $this->transactionService->update($uid, $request->validated(), $request->user()->uid);

            return redirect()->route('transactions.index')->with('success', 'Transação atualizada com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Failed to update transaction', ['error' => $e->getMessage()]);

            return back()->with('error', 'Erro ao atualizar transação.');
        }
    }

    public function destroy(Request $request, string $uid): RedirectResponse
    {
        try {
            $this->transactionService->delete($uid, $request->user()->uid);

            return redirect()->route('transactions.index')->with('success', 'Transação excluída com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Failed to delete transaction', ['error' => $e->getMessage()]);

            return back()->with('error', 'Erro ao excluir transação.');
        }
    }
}
