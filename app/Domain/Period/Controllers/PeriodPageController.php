<?php

namespace App\Domain\Period\Controllers;

use App\Domain\Account\Contracts\AccountServiceInterface;
use App\Domain\Category\Contracts\CategoryServiceInterface;
use App\Domain\Period\Contracts\PeriodServiceInterface;
use App\Domain\Period\Exceptions\PeriodAlreadyExistsException;
use App\Domain\Period\Exceptions\PeriodHasPaidTransactionsException;
use App\Domain\Period\Pdf\PeriodReportPdf;
use App\Domain\Period\Requests\StorePeriodRequest;
use App\Domain\Transaction\Contracts\TransactionServiceInterface;
use App\Domain\Transaction\Requests\StoreTransactionRequest;
use App\Domain\Transaction\Requests\UpdateTransactionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PeriodPageController
{
    public function __construct(
        private readonly PeriodServiceInterface $periodService,
        private readonly AccountServiceInterface $accountService,
        private readonly CategoryServiceInterface $categoryService,
        private readonly TransactionServiceInterface $transactionService,
    ) {}

    public function index(Request $request): InertiaResponse
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

    public function show(Request $request, string $uid): InertiaResponse
    {
        $userUid = $request->user()->uid;

        $periodSummary = $this->periodService->getByUidWithSummary($uid, $userUid);

        abort_unless($periodSummary, 404);

        $transactions = $this->periodService->getTransactionsForPeriod($uid, $userUid);

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
            'transactions' => $transactions,
            'accounts' => $accounts,
            'categories' => $categories,
            'fixedExpenses' => $fixedExpenses,
            'installments' => $installments,
            'cardBreakdown' => $cardBreakdown,
        ]);
    }

    public function report(Request $request, string $uid): Response
    {
        $userUid = $request->user()->uid;

        try {
            $periodSummary = $this->periodService->getByUidWithSummary($uid, $userUid);
            abort_unless($periodSummary, 404);

            $transactions = $this->periodService->getTransactionsForPeriod($uid, $userUid);
            $fixedExpenses = $this->periodService->getFixedExpensesForPeriod($uid, $userUid);
            $installments = $this->periodService->getInstallmentsForPeriod($uid, $userUid);
            $cardBreakdown = $this->periodService->getCardBreakdownForPeriod($uid, $userUid);

            $inflowTransactions = array_values(array_filter($transactions, fn ($t) => $t->direction === 'INFLOW'));
            $outflowTransactions = array_values(array_filter($transactions, fn ($t) => $t->direction === 'OUTFLOW'));

            $report = new PeriodReportPdf([
                'period' => $periodSummary['period'],
                'summary' => $periodSummary,
                'fixedExpenses' => $fixedExpenses,
                'installments' => $installments,
                'cardBreakdown' => $cardBreakdown,
                'inflowTransactions' => $inflowTransactions,
                'outflowTransactions' => $outflowTransactions,
            ]);

            return $report->generate();
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Failed to generate period report', [
                'period_uid' => $uid,
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Erro ao gerar relatório.');
        }
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

    public function updateTransaction(UpdateTransactionRequest $request, string $uid, string $transactionUid): RedirectResponse
    {
        try {
            $this->transactionService->update($transactionUid, $request->validated(), $request->user()->uid);

            return redirect("/periods/{$uid}")->with('success', 'Transação atualizada com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Failed to update transaction for period', [
                'period_uid' => $uid,
                'transaction_uid' => $transactionUid,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Erro ao atualizar transação.');
        }
    }

    public function destroyTransaction(Request $request, string $uid, string $transactionUid): RedirectResponse
    {
        try {
            $this->transactionService->delete($transactionUid, $request->user()->uid);

            return redirect("/periods/{$uid}")->with('success', 'Transação excluída com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Failed to delete transaction for period', [
                'period_uid' => $uid,
                'transaction_uid' => $transactionUid,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Erro ao excluir transação.');
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
