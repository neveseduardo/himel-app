<?php

namespace App\Domain\FixedExpense\Controllers;

use App\Domain\Category\Contracts\CategoryServiceInterface;
use App\Domain\FixedExpense\Contracts\FixedExpenseServiceInterface;
use App\Domain\FixedExpense\Requests\StoreFixedExpenseRequest;
use App\Domain\FixedExpense\Requests\UpdateFixedExpenseRequest;
use App\Domain\FixedExpense\Resources\FixedExpenseResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class FixedExpensePageController
{
    public function __construct(
        private readonly FixedExpenseServiceInterface $fixedExpenseService,
        private readonly CategoryServiceInterface $categoryService,
    ) {}

    public function index(Request $request): Response
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'active', 'category_uid', 'search']);
        $result = $this->fixedExpenseService->getAllWithFilters($userUid, $filters);

        return Inertia::render('finance/fixed-expenses/Index', [
            'fixedExpenses' => FixedExpenseResource::collection($result['data']),
            'meta' => $result['meta'],
            'filters' => $filters,
            'categories' => fn () => $this->categoryService->getByDirection($userUid, 'OUTFLOW'),
        ]);
    }

    public function store(StoreFixedExpenseRequest $request): RedirectResponse
    {
        try {
            $this->fixedExpenseService->create($request->validated(), $request->user()->uid);

            return redirect()->route('finance.fixed-expenses.index')->with('success', 'Despesa fixa criada com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Failed to create fixed expense', ['error' => $e->getMessage()]);

            return back()->with('error', 'Erro ao criar despesa fixa.');
        }
    }

    public function update(UpdateFixedExpenseRequest $request, string $uid): RedirectResponse
    {
        try {
            $this->fixedExpenseService->update($uid, $request->validated(), $request->user()->uid);

            return redirect()->route('finance.fixed-expenses.index')->with('success', 'Despesa fixa atualizada com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Failed to update fixed expense', ['error' => $e->getMessage()]);

            return back()->with('error', 'Erro ao atualizar despesa fixa.');
        }
    }

    public function destroy(Request $request, string $uid): RedirectResponse
    {
        try {
            $this->fixedExpenseService->delete($uid, $request->user()->uid);

            return redirect()->route('finance.fixed-expenses.index')->with('success', 'Despesa fixa excluída com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Failed to delete fixed expense', ['error' => $e->getMessage()]);

            return back()->with('error', 'Erro ao excluir despesa fixa.');
        }
    }
}
