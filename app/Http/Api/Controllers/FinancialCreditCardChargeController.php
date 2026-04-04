<?php

namespace App\Http\Api\Controllers;

use App\Http\Requests\StoreFinancialCreditCardChargeRequest;
use App\Http\Requests\UpdateFinancialCreditCardChargeRequest;
use App\Services\Interfaces\IChargeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinancialCreditCardChargeController
{
    public function __construct(
        private readonly IChargeService $chargeService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'card_uid', 'search']);
        $result = $this->chargeService->getAllWithFilters($userUid, $filters);

        return response()->json($result);
    }

    public function store(StoreFinancialCreditCardChargeRequest $request): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;
            $charge = $this->chargeService->create($request->validated(), $userUid);

            return response()->json(['data' => $charge], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao criar compra.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Request $request, string $uid): JsonResponse
    {
        $userUid = $request->user()->uid;
        $charge = $this->chargeService->getByUid($uid, $userUid);

        if (! $charge) {
            return response()->json(['error' => 'Compra não encontrada.'], 404);
        }

        return response()->json(['data' => $charge]);
    }

    public function update(UpdateFinancialCreditCardChargeRequest $request, string $uid): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;
            $charge = $this->chargeService->update($uid, $request->validated(), $userUid);

            if (! $charge) {
                return response()->json(['error' => 'Compra não encontrada.'], 404);
            }

            return response()->json(['data' => $charge]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao atualizar compra.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(Request $request, string $uid): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;
            $deleted = $this->chargeService->delete($uid, $userUid);

            if (! $deleted) {
                return response()->json(['error' => 'Compra não encontrada.'], 404);
            }

            return response()->json(null, 204);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao excluir compra.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
