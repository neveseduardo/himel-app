<?php

namespace App\Domain\CreditCardCharge\Controllers;

use App\Domain\CreditCardCharge\Contracts\CreditCardChargeServiceInterface;
use App\Domain\CreditCardCharge\Requests\StoreCreditCardChargeRequest;
use App\Domain\CreditCardCharge\Requests\UpdateCreditCardChargeRequest;
use App\Domain\CreditCardCharge\Resources\CreditCardChargeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CreditCardChargeController
{
    public function __construct(
        private readonly CreditCardChargeServiceInterface $creditCardChargeService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'card_uid', 'search']);
        $result = $this->creditCardChargeService->getAllWithFilters($userUid, $filters);
        $result['data'] = CreditCardChargeResource::collection($result['data']);

        return response()->json($result);
    }

    public function store(StoreCreditCardChargeRequest $request): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;

            $charge = $this->creditCardChargeService->create($request->validated(), $userUid);

            return response()->json(['data' => new CreditCardChargeResource($charge)], 201);
        } catch (\Throwable $e) {
            Log::error('Failed to create credit card charge', [
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Erro ao criar compra.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Request $request, string $uid): JsonResponse
    {
        $userUid = $request->user()->uid;
        $charge = $this->creditCardChargeService->getByUid($uid, $userUid);

        if (! $charge) {
            return response()->json(['error' => 'Compra não encontrada.'], 404);
        }

        return response()->json(['data' => new CreditCardChargeResource($charge)]);
    }

    public function update(UpdateCreditCardChargeRequest $request, string $uid): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;

            $charge = $this->creditCardChargeService->update($uid, $request->validated(), $userUid);

            if (! $charge) {
                return response()->json(['error' => 'Compra não encontrada.'], 404);
            }

            return response()->json(['data' => new CreditCardChargeResource($charge)]);
        } catch (\Throwable $e) {
            Log::error('Failed to update credit card charge', [
                'uid' => $uid,
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

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

            $deleted = $this->creditCardChargeService->delete($uid, $userUid);

            if (! $deleted) {
                return response()->json(['error' => 'Compra não encontrada.'], 404);
            }

            return response()->json(null, 204);
        } catch (\Throwable $e) {
            Log::error('Failed to delete credit card charge', [
                'uid' => $uid,
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Erro ao excluir compra.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
