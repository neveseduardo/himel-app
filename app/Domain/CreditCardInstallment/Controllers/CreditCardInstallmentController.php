<?php

namespace App\Domain\CreditCardInstallment\Controllers;

use App\Domain\CreditCardInstallment\Contracts\CreditCardInstallmentServiceInterface;
use App\Domain\CreditCardInstallment\Resources\CreditCardInstallmentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CreditCardInstallmentController
{
    public function __construct(
        private readonly CreditCardInstallmentServiceInterface $creditCardInstallmentService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'charge_uid', 'paid', 'date_from', 'date_to']);
        $result = $this->creditCardInstallmentService->getAllWithFilters($userUid, $filters);
        $result['data'] = CreditCardInstallmentResource::collection($result['data']);

        return response()->json($result);
    }

    public function show(Request $request, string $uid): JsonResponse
    {
        $userUid = $request->user()->uid;
        $installment = $this->creditCardInstallmentService->getByUid($uid, $userUid);

        if (! $installment) {
            return response()->json(['error' => 'Parcela não encontrada.'], 404);
        }

        return response()->json(['data' => new CreditCardInstallmentResource($installment)]);
    }

    public function markAsPaid(Request $request, string $uid): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;

            $installment = $this->creditCardInstallmentService->markAsPaid($uid, $userUid);

            if (! $installment) {
                return response()->json(['error' => 'Parcela não encontrada.'], 404);
            }

            return response()->json(['data' => new CreditCardInstallmentResource($installment)]);
        } catch (\Throwable $e) {
            Log::error('Failed to mark credit card installment as paid', [
                'uid' => $uid,
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Erro ao marcar parcela como paga.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
