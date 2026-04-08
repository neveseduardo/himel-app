<?php

namespace App\Domain\Transfer\Controllers;

use App\Domain\Transfer\Contracts\TransferServiceInterface;
use App\Domain\Transfer\Requests\StoreTransferRequest;
use App\Domain\Transfer\Resources\TransferResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransferController
{
    public function __construct(
        private readonly TransferServiceInterface $transferService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'account_uid', 'date_from', 'date_to']);
        $result = $this->transferService->getAllWithFilters($userUid, $filters);
        $result['data'] = TransferResource::collection($result['data']);

        return response()->json($result);
    }

    public function store(StoreTransferRequest $request): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;

            $transfer = $this->transferService->create($request->validated(), $userUid);

            return response()->json(['data' => new TransferResource($transfer)], 201);
        } catch (\Throwable $e) {
            Log::error('Failed to create transfer', [
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

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

        return response()->json(['data' => new TransferResource($transfer)]);
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
            Log::error('Failed to delete transfer', [
                'uid' => $uid,
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Erro ao excluir transferência.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
