<?php

namespace App\Domain\Transfer\Controllers;

use App\Domain\Account\Contracts\AccountServiceInterface;
use App\Domain\Transfer\Contracts\TransferServiceInterface;
use App\Domain\Transfer\Requests\StoreTransferRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class TransferPageController
{
    public function __construct(
        private readonly TransferServiceInterface $transferService,
        private readonly AccountServiceInterface $accountService,
    ) {}

    public function index(Request $request): Response
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'account_uid', 'date_from', 'date_to']);
        $result = $this->transferService->getAllWithFilters($userUid, $filters);

        return Inertia::render('finance/transfers/Index', [
            'transfers' => $result['data'],
            'meta' => $result['meta'],
            'filters' => $filters,
            'accounts' => Inertia::optional(fn () => $this->accountService->getAll($userUid)),
        ]);
    }

    public function store(StoreTransferRequest $request): RedirectResponse
    {
        try {
            $this->transferService->create($request->validated(), $request->user()->uid);

            return redirect()->route('finance.transfers.index')->with('success', 'Transferência criada com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Failed to create transfer', ['error' => $e->getMessage()]);

            return back()->with('error', 'Erro ao criar transferência.');
        }
    }

    public function destroy(Request $request, string $uid): RedirectResponse
    {
        try {
            $this->transferService->delete($uid, $request->user()->uid);

            return redirect()->route('finance.transfers.index')->with('success', 'Transferência excluída com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Failed to delete transfer', ['error' => $e->getMessage()]);

            return back()->with('error', 'Erro ao excluir transferência.');
        }
    }
}
