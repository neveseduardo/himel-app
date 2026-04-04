<?php

namespace App\Http\Api\Controllers;

use App\Http\Requests\StoreFinancialAccountRequest;
use App\Http\Requests\UpdateFinancialAccountRequest;
use App\Services\Interfaces\IAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinancialAccountController
{
    public function __construct(
        private readonly IAccountService $accountService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'type', 'search']);
        $result = $this->accountService->getAllWithFilters($userUid, $filters);

        return response()->json($result);
    }

    public function store(StoreFinancialAccountRequest $request): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;
            $account = $this->accountService->create($request->validated(), $userUid);

            return response()->json(['data' => $account], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao criar conta.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Request $request, string $uid): JsonResponse
    {
        $userUid = $request->user()->uid;
        $account = $this->accountService->getByUid($uid, $userUid);

        if (! $account) {
            return response()->json(['error' => 'Conta não encontrada.'], 404);
        }

        return response()->json(['data' => $account]);
    }

    public function update(UpdateFinancialAccountRequest $request, string $uid): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;
            $account = $this->accountService->update($uid, $request->validated(), $userUid);

            if (! $account) {
                return response()->json(['error' => 'Conta não encontrada.'], 404);
            }

            return response()->json(['data' => $account]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao atualizar conta.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(Request $request, string $uid): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;
            $deleted = $this->accountService->delete($uid, $userUid);

            if (! $deleted) {
                return response()->json(['error' => 'Conta não encontrada.'], 404);
            }

            return response()->json(null, 204);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao excluir conta.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
