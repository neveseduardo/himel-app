---
inclusion: fileMatch
fileMatchPattern: "app/Domain/**/*.php,routes/**/*.php"
---

# Backend Laravel (API + Domain)

## Arquitetura DDD

Cada domínio segue o padrão:
- Controller → Service → Model
- Form Requests para validação
- Policies para autorização
- API Resources para serialização

## Padrão de Controller

```php
class AccountController extends Controller
{
    public function __construct(
        private AccountServiceInterface $accountService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $accounts = $this->accountService->list($request->user());
        return AccountResource::collection($accounts)->response();
    }

    public function store(StoreAccountRequest $request): JsonResponse
    {
        $account = $this->accountService->create($request->validated());
        return (new AccountResource($account))->response()->setStatusCode(201);
    }
}
```

## Regras

- MUST usar Form Requests para validação (Store/Update)
- MUST usar Policies para autorização
- MUST usar API Resources para respostas
- MUST usar Service classes para lógica de negócio
- MUST usar Contracts (interfaces) para services
- MUST registrar bindings no AppServiceProvider
- MUST usar constructor property promotion (PHP 8.4)
- MUST usar return types explícitos em todos os métodos
- MUST rodar `vendor/bin/pint --dirty --format agent` após alterações PHP

## Testes

- MUST usar PHPUnit (não Pest)
- MUST usar factories para criar models em testes
- MUST testar happy paths, failure paths e edge cases
- Rodar: `php artisan test --compact --filter=NomeDoTeste`

## Rotas

- Definidas em `app/Domain/<Domínio>/Routes/` ou `routes/`
- Usar named routes: `Route::name('accounts.index')`
- Wayfinder gera typed functions automaticamente para o frontend
