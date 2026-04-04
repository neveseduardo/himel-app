<?php

namespace App\Http\Api\Controllers;

use App\Http\Requests\StoreFinancialTransactionRequest;
use App\Http\Requests\UpdateFinancialTransactionRequest;
use App\Services\Interfaces\ITransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinancialTransactionController
{
    public function __construct(
        private readonly ITransactionService $transactionService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'status', 'direction', 'source', 'account_uid', 'category_uid', 'date_from', 'date_to', 'search']);
        $result = $this->transactionService->getAllWithFilters($userUid, $filters);

        return response()->json($result);
    }

    public function store(StoreFinancialTransactionRequest $request): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;
            $transaction = $this->transactionService->create($request->validated(), $userUid);

            return response()->json(['data' => $transaction], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao criar transação.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Request $request, string $uid): JsonResponse
    {
        $userUid = $request->user()->uid;
        $transaction = $this->transactionService->getByUid($uid, $userUid);

        if (! $transaction) {
            return response()->json(['error' => 'Transação não encontrada.'], 404);
        }

        return response()->json(['data' => $transaction]);
    }

    public function update(UpdateFinancialTransactionRequest $request, string $uid): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;
            $transaction = $this->transactionService->update($uid, $request->validated(), $userUid);

            if (! $transaction) {
                return response()->json(['error' => 'Transação não encontrada.'], 404);
            }

            return response()->json(['data' => $transaction]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao atualizar transação.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(Request $request, string $uid): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;
            $deleted = $this->transactionService->delete($uid, $userUid);

            if (! $deleted) {
                return response()->json(['error' => 'Transação não encontrada.'], 404);
            }

            return response()->json(null, 204);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao excluir transação.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
