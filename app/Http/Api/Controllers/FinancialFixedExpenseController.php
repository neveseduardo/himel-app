<?php

namespace App\Http\Api\Controllers;

use App\Http\Requests\StoreFinancialFixedExpenseRequest;
use App\Http\Requests\UpdateFinancialFixedExpenseRequest;
use App\Services\Interfaces\IFixedExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinancialFixedExpenseController
{
    public function __construct(
        private readonly IFixedExpenseService $fixedExpenseService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'active', 'category_uid', 'search']);
        $result = $this->fixedExpenseService->getAllWithFilters($userUid, $filters);

        return response()->json($result);
    }

    public function store(StoreFinancialFixedExpenseRequest $request): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;
            $expense = $this->fixedExpenseService->create($request->validated(), $userUid);

            return response()->json(['data' => $expense], 201);
        } catch (\Throwable $e) {
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

    public function update(UpdateFinancialFixedExpenseRequest $request, string $uid): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;
            $expense = $this->fixedExpenseService->update($uid, $request->validated(), $userUid);

            if (! $expense) {
                return response()->json(['error' => 'Despesa fixa não encontrada.'], 404);
            }

            return response()->json(['data' => $expense]);
        } catch (\Throwable $e) {
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
            $deleted = $this->fixedExpenseService->delete($uid, $userUid);

            if (! $deleted) {
                return response()->json(['error' => 'Despesa fixa não encontrada.'], 404);
            }

            return response()->json(null, 204);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao excluir despesa fixa.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
