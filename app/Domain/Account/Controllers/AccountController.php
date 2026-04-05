<?php

namespace App\Domain\Account\Controllers;

use App\Domain\Account\Contracts\AccountServiceInterface;
use App\Domain\Account\Requests\StoreAccountRequest;
use App\Domain\Account\Requests\UpdateAccountRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountController
{
    public function __construct(
        private readonly AccountServiceInterface $accountService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'type', 'search']);
        $result = $this->accountService->getAllWithFilters($userUid, $filters);

        return response()->json($result);
    }

    public function store(StoreAccountRequest $request): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;

            $account = DB::transaction(function () use ($request, $userUid) {
                return $this->accountService->create($request->validated(), $userUid);
            });

            return response()->json(['data' => $account], 201);
        } catch (\Throwable $e) {
            Log::error('Failed to create account', [
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Erro ao criar conta.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Request $request, string $uid): JsonResponse
    {
        $userUid = $request->user()->uid;
        $account = $this->accountService->getByUid($uid, $userUid);

        if (! $account) {
            return response()->json(['error' => 'Conta não encontrada.'], 404);
        }

        return response()->json(['data' => $account]);
    }

    public function update(UpdateAccountRequest $request, string $uid): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;

            $account = DB::transaction(function () use ($request, $uid, $userUid) {
                return $this->accountService->update($uid, $request->validated(), $userUid);
            });

            if (! $account) {
                return response()->json(['error' => 'Conta não encontrada.'], 404);
            }

            return response()->json(['data' => $account]);
        } catch (\Throwable $e) {
            Log::error('Failed to update account', [
                'uid' => $uid,
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Erro ao atualizar conta.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(Request $request, string $uid): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;

            $deleted = DB::transaction(function () use ($uid, $userUid) {
                return $this->accountService->delete($uid, $userUid);
            });

            if (! $deleted) {
                return response()->json(['error' => 'Conta não encontrada.'], 404);
            }

            return response()->json(null, 204);
        } catch (\Throwable $e) {
            Log::error('Failed to delete account', [
                'uid' => $uid,
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Erro ao excluir conta.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
