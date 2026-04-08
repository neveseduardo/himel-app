<?php

namespace App\Domain\Category\Controllers;

use App\Domain\Category\Contracts\CategoryServiceInterface;
use App\Domain\Category\Requests\StoreCategoryRequest;
use App\Domain\Category\Requests\UpdateCategoryRequest;
use App\Domain\Category\Resources\CategoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController
{
    public function __construct(
        private readonly CategoryServiceInterface $categoryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'direction', 'search']);
        $result = $this->categoryService->getAllWithFilters($userUid, $filters);
        $result['data'] = CategoryResource::collection($result['data']);

        return response()->json($result);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;

            $category = $this->categoryService->create($request->validated(), $userUid);

            return response()->json(['data' => new CategoryResource($category)], 201);
        } catch (\Throwable $e) {
            Log::error('Failed to create category', [
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

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

        return response()->json(['data' => new CategoryResource($category)]);
    }

    public function update(UpdateCategoryRequest $request, string $uid): JsonResponse
    {
        try {
            $userUid = $request->user()->uid;

            $category = $this->categoryService->update($uid, $request->validated(), $userUid);

            if (! $category) {
                return response()->json(['error' => 'Categoria não encontrada.'], 404);
            }

            return response()->json(['data' => new CategoryResource($category)]);
        } catch (\Throwable $e) {
            Log::error('Failed to update category', [
                'uid' => $uid,
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

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
            Log::error('Failed to delete category', [
                'uid' => $uid,
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Erro ao excluir categoria.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
