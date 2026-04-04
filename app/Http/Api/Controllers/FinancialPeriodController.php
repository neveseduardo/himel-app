<?php

namespace App\Http\Api\Controllers;

use App\Http\Requests\StoreFinancialPeriodRequest;
use App\Services\Interfaces\IPeriodService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinancialPeriodController
{
    public function __construct(
        private readonly IPeriodService $periodService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'month', 'year']);
        $result = $this->periodService->getAllWithFilters($userUid, $filters);

        return response()->json($result);
    }

    public function store(StoreFinancialPeriodRequest $request): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;
            $period = $this->periodService->getOrCreate(
                $userUid,
                $request->validated()['month'],
                $request->validated()['year']
            );

            return response()->json(['data' => $period], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao criar período.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Request $request, string $uid): JsonResponse
    {
        $userUid = $request->user()->uid;
        $period = $this->periodService->getByUid($uid, $userUid);

        if (! $period) {
            return response()->json(['error' => 'Período não encontrado.'], 404);
        }

        return response()->json(['data' => $period]);
    }

    public function destroy(Request $request, string $uid): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;
            $deleted = $this->periodService->delete($uid, $userUid);

            if (! $deleted) {
                return response()->json(['error' => 'Período não encontrado.'], 404);
            }

            return response()->json(null, 204);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao excluir período.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function current(Request $request): JsonResponse
    {
        $userUid = $request->user()->uid;
        $period = $this->periodService->getCurrent($userUid);

        if (! $period) {
            return response()->json(['error' => 'Período atual não encontrado.'], 404);
        }

        return response()->json(['data' => $period]);
    }
}
