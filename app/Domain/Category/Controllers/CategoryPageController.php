<?php

namespace App\Domain\Category\Controllers;

use App\Domain\Category\Contracts\CategoryServiceInterface;
use App\Domain\Category\Requests\StoreCategoryRequest;
use App\Domain\Category\Requests\UpdateCategoryRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class CategoryPageController
{
    public function __construct(
        private readonly CategoryServiceInterface $categoryService
    ) {}

    public function index(Request $request): Response
    {
        $userUid = $request->user()->uid;
        $filters = $request->only(['page', 'per_page', 'direction', 'search']);
        $result = $this->categoryService->getAllWithFilters($userUid, $filters);

        return Inertia::render('finance/categories/Index', [
            'categories' => $result['data'],
            'meta' => $result['meta'],
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('finance/categories/Create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        try {
            $this->categoryService->create($request->validated(), $request->user()->uid);

            return redirect()->route('finance.categories.index')->with('success', 'Categoria criada com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Failed to create category', ['error' => $e->getMessage()]);

            return back()->with('error', 'Erro ao criar categoria.');
        }
    }

    public function edit(Request $request, string $uid): Response
    {
        $category = $this->categoryService->getByUid($uid, $request->user()->uid);
        abort_unless($category, 404);

        return Inertia::render('finance/categories/Edit', ['category' => $category]);
    }

    public function update(UpdateCategoryRequest $request, string $uid): RedirectResponse
    {
        try {
            $this->categoryService->update($uid, $request->validated(), $request->user()->uid);

            return redirect()->route('finance.categories.index')->with('success', 'Categoria atualizada com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Failed to update category', ['error' => $e->getMessage()]);

            return back()->with('error', 'Erro ao atualizar categoria.');
        }
    }

    public function destroy(Request $request, string $uid): RedirectResponse
    {
        try {
            $this->categoryService->delete($uid, $request->user()->uid);

            return redirect()->route('finance.categories.index')->with('success', 'Categoria excluída com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Failed to delete category', ['error' => $e->getMessage()]);

            return back()->with('error', 'Erro ao excluir categoria.');
        }
    }
}
