<?php

namespace App\Domain\CreditCardInstallment\Controllers;

use App\Domain\CreditCardInstallment\Contracts\CreditCardInstallmentServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        return response()->json($result);
    }

    public function show(Request $request, string $uid): JsonResponse
    {
        $userUid = $request->user()->uid;
        $installment = $this->creditCardInstallmentService->getByUid($uid, $userUid);

        if (! $installment) {
            return response()->json(['error' => 'Parcela não encontrada.'], 404);
        }

        return response()->json(['data' => $installment]);
    }

    public function markAsPaid(Request $request, string $uid): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;

            $installment = DB::transaction(function () use ($uid, $userUid) {
                return $this->creditCardInstallmentService->markAsPaid($uid, $userUid);
            });

            if (! $installment) {
                return response()->json(['error' => 'Parcela não encontrada.'], 404);
            }

            return response()->json(['data' => $installment]);
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
