<?php

namespace App\Http\Api\Controllers;

use App\Http\Requests\StoreFinancialCreditCardRequest;
use App\Http\Requests\UpdateFinancialCreditCardRequest;
use App\Services\Interfaces\ICreditCardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinancialCreditCardController
{
    public function __construct(
        private readonly ICreditCardService $creditCardService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'card_type', 'search']);
        $result = $this->creditCardService->getAllWithFilters($userUid, $filters);

        return response()->json($result);
    }

    public function store(StoreFinancialCreditCardRequest $request): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;
            $card = $this->creditCardService->create($request->validated(), $userUid);

            return response()->json(['data' => $card], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao criar cartão de crédito.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Request $request, string $uid): JsonResponse
    {
        $userUid = $request->user()->uid;
        $card = $this->creditCardService->getByUid($uid, $userUid);

        if (! $card) {
            return response()->json(['error' => 'Cartão de crédito não encontrado.'], 404);
        }

        return response()->json(['data' => $card]);
    }

    public function update(UpdateFinancialCreditCardRequest $request, string $uid): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;
            $card = $this->creditCardService->update($uid, $request->validated(), $userUid);

            if (! $card) {
                return response()->json(['error' => 'Cartão de crédito não encontrado.'], 404);
            }

            return response()->json(['data' => $card]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao atualizar cartão de crédito.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(Request $request, string $uid): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;
            $deleted = $this->creditCardService->delete($uid, $userUid);

            if (! $deleted) {
                return response()->json(['error' => 'Cartão de crédito não encontrado.'], 404);
            }

            return response()->json(null, 204);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao excluir cartão de crédito.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
