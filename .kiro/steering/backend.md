# Regras de Backend — Himel App

> **Glob:** `app/**/*.php, database/**/*.php, routes/**/*.php`
>
> Regras obrigatórias para todo código PHP/Laravel do projeto.

## Service Layer

- Controllers NÃO DEVEM conter lógica de negócio. Toda regra DEVE residir no Service.
- Toda Service DEVE implementar uma Interface (em `Contracts/`).
- O Controller DEVE injetar a Interface da Service no construtor.
- Binding Interface → Service DEVE ser registrado no `AppServiceProvider`.

## Controllers

- Operações de escrita (`POST`, `PUT`, `DELETE`) DEVEM ter `try-catch` no Controller.
- O bloco `catch` DEVE registrar logs via `Log::error()` com contexto detalhado.
- Controllers DEVEM retornar `redirect()->back()` com flash messages (`success` ou `error`).
- PageControllers renderizam páginas Inertia; Controllers processam operações CRUD.

## Transacionalidade

- `DB::transaction` é responsabilidade EXCLUSIVA do Service Layer (NUNCA no Controller).
- Controllers delegam diretamente ao Service sem encapsular em transação.
- Operações compostas (ex: transferência, inicialização de período) DEVEM usar `DB::transaction`.

## Eloquent Models

- Toda Model DEVE incluir:
  ```php
  use HasUids;
  protected $primaryKey = 'uid';
  public $incrementing = false;
  protected $keyType = 'string';
  ```
- `$fillable` DEVE listar todos os campos editáveis.
- Relacionamentos DEVEM ser tipados com return type (`BelongsTo`, `HasMany`, etc.).
- Scopes reutilizáveis DEVEM ser criados para filtros comuns (ex: `forUser($uid)`).

## FormRequests

- Toda operação de escrita DEVE usar FormRequest dedicado (`Store{Entity}Request`, `Update{Entity}Request`).
- Mensagens de erro DEVEM ser em Português (pt-BR).
- Regras de validação DEVEM ser explícitas e completas.

## API Resources

- Toda serialização de dados para o frontend DEVE usar API Resources.
- Resources DEVEM expor apenas os campos necessários para o frontend.
- Relacionamentos DEVEM usar `whenLoaded()` para evitar N+1.

## Rotas

- Rotas web DEVEM ser definidas em `app/Domain/{Entity}/Routes/web.php`.
- Rotas DEVEM usar `Route::resource()` quando aplicável.
- Parâmetros de rota DEVEM usar `uid` como identificador.
- Rotas DEVEM ter nomes descritivos (ex: `finance.accounts.index`).

## Exceções de Domínio

- Erros de negócio DEVEM lançar exceções específicas (ex: `PeriodAlreadyExistsException`).
- Exceções DEVEM ser tratadas no Controller com mensagens amigáveis ao usuário.

## Filtros e Paginação

- Cada listagem DEVE suportar filtros e paginação no backend.
- Formato de resposta paginada: dados da página, total de registros, página atual, total de páginas.
- Eager loading DEVE ser usado para evitar N+1 em listagens com relacionamentos.
