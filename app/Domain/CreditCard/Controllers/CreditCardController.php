<?php

namespace App\Domain\CreditCard\Controllers;

use App\Domain\CreditCard\Contracts\CreditCardServiceInterface;
use App\Domain\CreditCard\Requests\StoreCreditCardRequest;
use App\Domain\CreditCard\Requests\UpdateCreditCardRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditCardController
{
    public function __construct(
        private readonly CreditCardServiceInterface $creditCardService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'card_type', 'search']);
        $result = $this->creditCardService->getAllWithFilters($userUid, $filters);

        return response()->json($result);
    }

    public function store(StoreCreditCardRequest $request): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;

            $card = DB::transaction(function () use ($request, $userUid) {
                return $this->creditCardService->create($request->validated(), $userUid);
            });

            return response()->json(['data' => $card], 201);
        } catch (\Throwable $e) {
            Log::error('Failed to create credit card', [
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

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

    public function update(UpdateCreditCardRequest $request, string $uid): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;

            $card = DB::transaction(function () use ($request, $uid, $userUid) {
                return $this->creditCardService->update($uid, $request->validated(), $userUid);
            });

            if (! $card) {
                return response()->json(['error' => 'Cartão de crédito não encontrado.'], 404);
            }

            return response()->json(['data' => $card]);
        } catch (\Throwable $e) {
            Log::error('Failed to update credit card', [
                'uid' => $uid,
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

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

            $deleted = DB::transaction(function () use ($uid, $userUid) {
                return $this->creditCardService->delete($uid, $userUid);
            });

            if (! $deleted) {
                return response()->json(['error' => 'Cartão de crédito não encontrado.'], 404);
            }

            return response()->json(null, 204);
        } catch (\Throwable $e) {
            Log::error('Failed to delete credit card', [
                'uid' => $uid,
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Erro ao excluir cartão de crédito.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
