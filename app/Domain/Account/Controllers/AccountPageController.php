<?php

namespace App\Domain\Account\Controllers;

use App\Domain\Account\Contracts\AccountServiceInterface;
use App\Domain\Account\Requests\StoreAccountRequest;
use App\Domain\Account\Requests\UpdateAccountRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class AccountPageController
{
    public function __construct(
        private readonly AccountServiceInterface $accountService
    ) {}

    public function index(Request $request): Response
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'type', 'search']);
        $result = $this->accountService->getAllWithFilters($userUid, $filters);

        return Inertia::render('accounts/Index', [
            'accounts' => $result['data'],
            'meta' => $result['meta'],
            'filters' => $filters,
        ]);
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        try {
            $this->accountService->create($request->validated(), $request->user()->uid);

            return redirect()->route('accounts.index')->with('success', 'Conta criada com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Failed to create account', ['error' => $e->getMessage()]);

            return back()->with('error', 'Erro ao criar conta.');
        }
    }

    public function update(UpdateAccountRequest $request, string $uid): RedirectResponse
    {
        try {
            $this->accountService->update($uid, $request->validated(), $request->user()->uid);

            return redirect()->route('accounts.index')->with('success', 'Conta atualizada com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Failed to update account', ['error' => $e->getMessage()]);

            return back()->with('error', 'Erro ao atualizar conta.');
        }
    }

    public function destroy(Request $request, string $uid): RedirectResponse
    {
        try {
            $this->accountService->delete($uid, $request->user()->uid);

            return redirect()->route('accounts.index')->with('success', 'Conta excluída com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Failed to delete account', ['error' => $e->getMessage()]);

            return back()->with('error', 'Erro ao excluir conta.');
        }
    }
}
