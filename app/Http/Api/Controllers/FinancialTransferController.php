<?php

namespace App\Http\Api\Controllers;

use App\Http\Requests\StoreFinancialTransferRequest;
use App\Services\Interfaces\ITransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinancialTransferController
{
    public function __construct(
        private readonly ITransferService $transferService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'account_uid', 'date_from', 'date_to']);
        $result = $this->transferService->getAllWithFilters($userUid, $filters);

        return response()->json($result);
    }

    public function store(StoreFinancialTransferRequest $request): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;
            $transfer = $this->transferService->create($request->validated(), $userUid);

            return response()->json(['data' => $transfer], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao criar transferência.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Request $request, string $uid): JsonResponse
    {
        $userUid = $request->user()->uid;
        $transfer = $this->transferService->getByUid($uid, $userUid);

        if (! $transfer) {
            return response()->json(['error' => 'Transferência não encontrada.'], 404);
        }

        return response()->json(['data' => $transfer]);
    }

    public function destroy(Request $request, string $uid): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;
            $deleted = $this->transferService->delete($uid, $userUid);

            if (! $deleted) {
                return response()->json(['error' => 'Transferência não encontrada.'], 404);
            }

            return response()->json(null, 204);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao excluir transferência.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
