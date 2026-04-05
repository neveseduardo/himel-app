<?php

namespace App\Domain\Period\Controllers;

use App\Domain\Period\Contracts\PeriodServiceInterface;
use App\Domain\Period\Requests\StorePeriodRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PeriodController
{
    public function __construct(
        private readonly PeriodServiceInterface $periodService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'month', 'year']);
        $result = $this->periodService->getAllWithFilters($userUid, $filters);

        return response()->json($result);
    }

    public function store(StorePeriodRequest $request): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;

            $period = DB::transaction(function () use ($request, $userUid) {
                return $this->periodService->getOrCreate(
                    $userUid,
                    $request->validated()['month'],
                    $request->validated()['year']
                );
            });

            return response()->json(['data' => $period], 201);
        } catch (\Throwable $e) {
            Log::error('Failed to create period', [
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

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

            $deleted = DB::transaction(function () use ($uid, $userUid) {
                return $this->periodService->delete($uid, $userUid);
            });

            if (! $deleted) {
                return response()->json(['error' => 'Período não encontrado.'], 404);
            }

            return response()->json(null, 204);
        } catch (\Throwable $e) {
            Log::error('Failed to delete period', [
                'uid' => $uid,
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

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
