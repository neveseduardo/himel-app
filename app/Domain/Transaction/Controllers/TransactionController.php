<?php

namespace App\Domain\Transaction\Controllers;

use App\Domain\Transaction\Contracts\TransactionServiceInterface;
use App\Domain\Transaction\Requests\StoreTransactionRequest;
use App\Domain\Transaction\Requests\UpdateTransactionRequest;
use App\Domain\Transaction\Resources\TransactionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransactionController
{
    public function __construct(
        private readonly TransactionServiceInterface $transactionService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'status', 'direction', 'source', 'account_uid', 'category_uid', 'date_from', 'date_to', 'search']);
        $result = $this->transactionService->getAllWithFilters($userUid, $filters);
        $result['data'] = TransactionResource::collection($result['data']);

        return response()->json($result);
    }

    public function store(StoreTransactionRequest $request): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;

            $transaction = $this->transactionService->create($request->validated(), $userUid);

            return response()->json(['data' => new TransactionResource($transaction)], 201);
        } catch (\Throwable $e) {
            Log::error('Failed to create transaction', [
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

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

        return response()->json(['data' => new TransactionResource($transaction)]);
    }

    public function update(UpdateTransactionRequest $request, string $uid): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;

            $transaction = $this->transactionService->update($uid, $request->validated(), $userUid);

            if (! $transaction) {
                return response()->json(['error' => 'Transação não encontrada.'], 404);
            }

            return response()->json(['data' => new TransactionResource($transaction)]);
        } catch (\Throwable $e) {
            Log::error('Failed to update transaction', [
                'uid' => $uid,
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

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
            Log::error('Failed to delete transaction', [
                'uid' => $uid,
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Erro ao excluir transação.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
