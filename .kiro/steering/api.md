---
inclusion: fileMatch
fileMatchPattern: "app/Domain/**/Controllers/*.php,app/Domain/**/Routes/*.php"
priority: 50
---

# Padrões de API e Endpoints — Himel App

> Padrões para endpoints, respostas e comunicação backend ↔ frontend via Inertia.js.

## Comunicação: Inertia.js (Exclusiva)

O frontend se comunica com o backend exclusivamente via Inertia.js. NÃO existe API REST separada.

### Padrão de Respostas Inertia

- Listagens: `Inertia::render('page', ['items' => Resource::collection($data), 'meta' => $meta, 'filters' => $filters])`
- Operações de escrita: `redirect()->back()->with('success', 'Mensagem')` ou `->with('error', 'Mensagem')`
- Erros de validação: retornados automaticamente pelo FormRequest (422)

### Padrão de Controllers

```php
// PageController — renderiza páginas
class EntityPageController
{
    public function index(Request $request): Response
    public function show(Request $request, string $uid): Response
}

// Controller — operações CRUD
class EntityController
{
    public function store(StoreEntityRequest $request): RedirectResponse
    public function update(UpdateEntityRequest $request, string $uid): RedirectResponse
    public function destroy(Request $request, string $uid): RedirectResponse
}
```

## Padrão de Rotas

```php
// app/Domain/{Entity}/Routes/web.php
Route::prefix('finance')->middleware(['auth'])->group(function () {
    Route::get('{entities}', [EntityPageController::class, 'index'])->name('finance.entities.index');
    Route::get('{entities}/{uid}', [EntityPageController::class, 'show'])->name('finance.entities.show');
    Route::post('{entities}', [EntityController::class, 'store'])->name('finance.entities.store');
    Route::put('{entities}/{uid}', [EntityController::class, 'update'])->name('finance.entities.update');
    Route::delete('{entities}/{uid}', [EntityController::class, 'destroy'])->name('finance.entities.destroy');
});
```

## Padrão de Respostas Paginadas

```php
return Inertia::render('finance/entities/Index', [
    'items' => EntityResource::collection($paginated),
    'meta' => [
        'current_page' => $paginated->currentPage(),
        'per_page' => $paginated->perPage(),
        'total' => $paginated->total(),
        'last_page' => $paginated->lastPage(),
    ],
    'filters' => $request->only(['search', 'status', 'direction']),
]);
```

## Padrão de Erros

| Código | Cenário | Resposta |
|--------|---------|----------|
| 422 | Validação falhou | Erros por campo (automático via FormRequest) |
| 409 | Conflito (ex: período duplicado) | `redirect()->back()->with('error', $message)` |
| 403 | Não autorizado | Tratado via Policy |
| 404 | Recurso não encontrado | Página 404 padrão do Laravel |
| 500 | Erro interno | `redirect()->back()->with('error', 'Erro genérico')` + `Log::error()` |

## Wayfinder (Frontend)

Rotas DEVEM ser consumidas no frontend exclusivamente via Wayfinder:

```typescript
import { store, update, destroy } from '@/actions/App/Domain/Entity/Controllers/EntityController'

store.url()              // POST /finance/entities
update.url({ uid })      // PUT /finance/entities/{uid}
destroy.url({ uid })     // DELETE /finance/entities/{uid}
```

URLs em string pura são PROIBIDAS no frontend.
