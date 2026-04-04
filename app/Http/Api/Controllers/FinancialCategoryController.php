<?php

namespace App\Http\Api\Controllers;

use App\Http\Requests\StoreFinancialCategoryRequest;
use App\Http\Requests\UpdateFinancialCategoryRequest;
use App\Services\Interfaces\ICategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinancialCategoryController
{
    public function __construct(
        private readonly ICategoryService $categoryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'direction', 'search']);
        $result = $this->categoryService->getAllWithFilters($userUid, $filters);

        return response()->json($result);
    }

    public function store(StoreFinancialCategoryRequest $request): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;
            $category = $this->categoryService->create($request->validated(), $userUid);

            return response()->json(['data' => $category], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao criar categoria.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Request $request, string $uid): JsonResponse
    {
        $userUid = $request->user()->uid;
        $category = $this->categoryService->getByUid($uid, $userUid);

        if (! $category) {
            return response()->json(['error' => 'Categoria não encontrada.'], 404);
        }

        return response()->json(['data' => $category]);
    }

    public function update(UpdateFinancialCategoryRequest $request, string $uid): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;
            $category = $this->categoryService->update($uid, $request->validated(), $userUid);

            if (! $category) {
                return response()->json(['error' => 'Categoria não encontrada.'], 404);
            }

            return response()->json(['data' => $category]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao atualizar categoria.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(Request $request, string $uid): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;
            $deleted = $this->categoryService->delete($uid, $userUid);

            if (! $deleted) {
                return response()->json(['error' => 'Categoria não encontrada.'], 404);
            }

            return response()->json(null, 204);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao excluir categoria.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
