<?php

namespace App\Domain\FixedExpense\Controllers;

use App\Domain\FixedExpense\Contracts\FixedExpenseServiceInterface;
use App\Domain\FixedExpense\Requests\StoreFixedExpenseRequest;
use App\Domain\FixedExpense\Requests\UpdateFixedExpenseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixedExpenseController
{
    public function __construct(
        private readonly FixedExpenseServiceInterface $fixedExpenseService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'active', 'category_uid', 'search']);
        $result = $this->fixedExpenseService->getAllWithFilters($userUid, $filters);

        return response()->json($result);
    }

    public function store(StoreFixedExpenseRequest $request): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;

            $expense = DB::transaction(function () use ($request, $userUid) {
                return $this->fixedExpenseService->create($request->validated(), $userUid);
            });

            return response()->json(['data' => $expense], 201);
        } catch (\Throwable $e) {
            Log::error('Failed to create fixed expense', [
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Erro ao criar despesa fixa.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Request $request, string $uid): JsonResponse
    {
        $userUid = $request->user()->uid;
        $expense = $this->fixedExpenseService->getByUid($uid, $userUid);

        if (! $expense) {
            return response()->json(['error' => 'Despesa fixa não encontrada.'], 404);
        }

        return response()->json(['data' => $expense]);
    }

    public function update(UpdateFixedExpenseRequest $request, string $uid): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;

            $expense = DB::transaction(function () use ($request, $uid, $userUid) {
                return $this->fixedExpenseService->update($uid, $request->validated(), $userUid);
            });

            if (! $expense) {
                return response()->json(['error' => 'Despesa fixa não encontrada.'], 404);
            }

            return response()->json(['data' => $expense]);
        } catch (\Throwable $e) {
            Log::error('Failed to update fixed expense', [
                'uid' => $uid,
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Erro ao atualizar despesa fixa.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(Request $request, string $uid): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;

            $deleted = DB::transaction(function () use ($uid, $userUid) {
                return $this->fixedExpenseService->delete($uid, $userUid);
            });

            if (! $deleted) {
                return response()->json(['error' => 'Despesa fixa não encontrada.'], 404);
            }

            return response()->json(null, 204);
        } catch (\Throwable $e) {
            Log::error('Failed to delete fixed expense', [
                'uid' => $uid,
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Erro ao excluir despesa fixa.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
