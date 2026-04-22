<?php

namespace App\Domain\Period\Controllers;

use App\Domain\Account\Contracts\AccountServiceInterface;
use App\Domain\Category\Contracts\CategoryServiceInterface;
use App\Domain\Period\Contracts\PeriodServiceInterface;
use App\Domain\Period\Exceptions\PeriodAlreadyExistsException;
use App\Domain\Period\Exceptions\PeriodHasPaidTransactionsException;
use App\Domain\Period\Requests\StorePeriodRequest;
use App\Domain\Transaction\Contracts\TransactionServiceInterface;
use App\Domain\Transaction\Requests\StoreTransactionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class PeriodPageController
{
    public function __construct(
        private readonly PeriodServiceInterface $periodService,
        private readonly AccountServiceInterface $accountService,
        private readonly CategoryServiceInterface $categoryService,
        private readonly TransactionServiceInterface $transactionService,
    ) {}

    public function index(Request $request): Response
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'month', 'year']);
        $result = $this->periodService->getAllWithFilters($userUid, $filters);

        return Inertia::render('periods/Index', [
            'periods' => $result['data'],
            'meta' => $result['meta'],
            'filters' => $filters,
        ]);
    }

    public function store(StorePeriodRequest $request): RedirectResponse
    {
        try {
            $this->periodService->create(
                $request->user()->uid,
                $request->validated('month'),
                $request->validated('year'),
            );

            return redirect()->route('periods.index')->with('success', 'Período criado com sucesso.');
        } catch (PeriodAlreadyExistsException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Failed to create period', ['error' => $e->getMessage()]);

            return back()->with('error', 'Erro ao criar período.');
        }
    }

    public function show(Request $request, string $uid): Response
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['status', 'direction', 'source']);

        $periodSummary = $this->periodService->getByUidWithSummary($uid, $userUid);

        abort_unless($periodSummary, 404);

        $transactions = $this->periodService->getTransactionsForPeriod($uid, $userUid, $filters);

        $accounts = $this->accountService->getAll($userUid);
        $categories = $this->categoryService->getAll($userUid);

        $fixedExpenses = $this->periodService->getFixedExpensesForPeriod($uid, $userUid);
        $installments = $this->periodService->getInstallmentsForPeriod($uid, $userUid);
        $cardBreakdown = $this->periodService->getCardBreakdownForPeriod($uid, $userUid);

        return Inertia::render('periods/Show', [
            'period' => $periodSummary['period'],
            'summary' => [
                'total_inflow' => $periodSummary['total_inflow'],
                'total_outflow' => $periodSummary['total_outflow'],
                'balance' => $periodSummary['balance'],
                'total_fixed_expenses' => $periodSummary['total_fixed_expenses'],
                'total_credit_card_installments' => $periodSummary['total_credit_card_installments'],
                'total_manual' => $periodSummary['total_manual'],
                'total_transfer' => $periodSummary['total_transfer'],
                'inflow_manual' => $periodSummary['inflow_manual'],
                'inflow_transfer' => $periodSummary['inflow_transfer'],
            ],
            'transactions' => $transactions['data'],
            'meta' => $transactions['meta'],
            'filters' => $filters,
            'accounts' => $accounts,
            'categories' => $categories,
            'fixedExpenses' => $fixedExpenses,
            'installments' => $installments,
            'cardBreakdown' => $cardBreakdown,
        ]);
    }

    public function destroy(Request $request, string $uid): RedirectResponse
    {
        try {
            $this->periodService->delete($uid, $request->user()->uid);

            return redirect()->route('periods.index')->with('success', 'Período excluído com sucesso.');
        } catch (PeriodHasPaidTransactionsException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Failed to delete period', ['error' => $e->getMessage()]);

            return back()->with('error', 'Erro ao excluir período.');
        }
    }

    public function storeTransaction(StoreTransactionRequest $request, string $uid): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['period_uid'] = $uid;

            $this->transactionService->create($data, $request->user()->uid);

            return redirect("/periods/{$uid}")->with('success', 'Transação criada com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Failed to create transaction for period', [
                'period_uid' => $uid,
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Erro ao criar transação.');
        }
    }

    public function detachTransactions(Request $request, string $uid): RedirectResponse
    {
        try {
            $count = $this->periodService->detachAllTransactions($uid, $request->user()->uid);

            return redirect("/periods/{$uid}")
                ->with('success', sprintf('%d transação(ões) desvinculada(s) do período.', $count));
        } catch (\Throwable $e) {
            Log::error('Failed to detach transactions from period', [
                'period_uid' => $uid,
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Erro ao desvincular transações do período.');
        }
    }

    public function initialize(Request $request, string $uid): RedirectResponse
    {
        try {
            $result = $this->periodService->initializePeriod($uid, $request->user()->uid);

            $message = sprintf(
                'Período inicializado: %d despesas fixas criadas, %d parcelas vinculadas, %d parcelas criadas, %d ignorados.',
                $result['fixed_created'],
                $result['installments_linked'],
                $result['installments_created'],
                $result['skipped'],
            );

            return redirect("/periods/{$uid}")->with('success', $message);
        } catch (\Throwable $e) {
            Log::error('Failed to initialize period', [
                'uid' => $uid,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Erro ao inicializar período.');
        }
    }
}
