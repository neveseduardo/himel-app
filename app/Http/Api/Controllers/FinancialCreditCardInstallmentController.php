<?php

namespace App\Http\Api\Controllers;

use App\Services\Interfaces\IInstallmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinancialCreditCardInstallmentController
{
    public function __construct(
        private readonly IInstallmentService $installmentService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'charge_uid', 'paid', 'date_from', 'date_to']);
        $result = $this->installmentService->getAllWithFilters($userUid, $filters);

        return response()->json($result);
    }

    public function show(Request $request, string $uid): JsonResponse
    {
        $userUid = $request->user()->uid;
        $installment = $this->installmentService->getByUid($uid, $userUid);

        if (! $installment) {
            return response()->json(['error' => 'Parcela não encontrada.'], 404);
        }

        return response()->json(['data' => $installment]);
    }

    public function markAsPaid(Request $request, string $uid): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;
            $installment = $this->installmentService->markAsPaid($uid, $userUid);

            if (! $installment) {
                return response()->json(['error' => 'Parcela não encontrada.'], 404);
            }

            return response()->json(['data' => $installment]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao marcar parcela como paga.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
