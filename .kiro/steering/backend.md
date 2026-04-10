---
inclusion: fileMatch
fileMatchPattern: "app/**/*.php,database/**/*.php,routes/**/*.php"
priority: 70
---

# Regras de Backend — Himel App

> Regras obrigatórias para todo código PHP/Laravel do projeto.
> Lógica de negócio financeira DEVE residir exclusivamente no backend (Service Layer).

## Service Layer — Dono da Lógica de Negócio

- Controllers NUNCA DEVEM conter lógica de negócio. Toda regra DEVE residir no Service.
- Toda Service DEVE implementar uma Interface (em `Contracts/`).
- O Controller DEVE injetar a Interface da Service no construtor.
- Binding Interface → Service DEVE ser registrado no `AppServiceProvider`.
- Cálculos financeiros (saldo, parcelas, projeções) DEVEM existir APENAS no Service.
- O frontend NUNCA DEVE calcular valores financeiros — apenas exibir o que o backend retorna.

## Controllers

- Operações de escrita (`POST`, `PUT`, `DELETE`) DEVEM ter `try-catch` no Controller.
- O bloco `catch` DEVE registrar logs via `Log::error()` com contexto detalhado.
- Controllers DEVEM retornar `redirect()->back()` com flash messages (`success` ou `error`).
- PageControllers renderizam páginas Inertia; Controllers processam operações CRUD.
- Controllers NUNCA DEVEM acessar `DB::` diretamente — delegar ao Service.

## Transacionalidade

- `DB::transaction` é responsabilidade EXCLUSIVA do Service Layer.
- Controllers NUNCA DEVEM encapsular chamadas em `DB::transaction`.
- Operações compostas (transferência, inicialização de período, criação de parcelas) DEVEM usar `DB::transaction`.

## Eloquent Models

- Toda Model DEVE incluir `HasUids`, `$primaryKey = 'uid'`, `$incrementing = false`, `$keyType = 'string'`.
- `$fillable` DEVE listar todos os campos editáveis.
- Relacionamentos DEVEM ser tipados com return type (`BelongsTo`, `HasMany`, etc.).
- Scopes reutilizáveis DEVEM ser criados para filtros comuns (ex: `forUser($uid)`).
- Toda query que retorna dados financeiros DEVE usar scope `forUser()` para isolamento.

## FormRequests

- Toda operação de escrita DEVE usar FormRequest dedicado (`Store{Entity}Request`, `Update{Entity}Request`).
- Mensagens de erro DEVEM ser em Português (pt-BR).
- Regras de validação DEVEM ser explícitas e completas.
- FormRequests DEVEM validar ownership de recursos referenciados (ex: conta pertence ao usuário).

## API Resources

- Toda serialização de dados para o frontend DEVE usar API Resources.
- Resources DEVEM expor apenas os campos necessários para o frontend.
- Relacionamentos DEVEM usar `whenLoaded()` para evitar N+1.
- Resources NUNCA DEVEM expor `user_id` ou dados internos sensíveis.

## Rotas

- Rotas web DEVEM ser definidas em `app/Domain/{Entity}/Routes/web.php`.
- Rotas DEVEM usar `Route::resource()` quando aplicável.
- Parâmetros de rota DEVEM usar `uid` como identificador.
- Rotas DEVEM ter nomes descritivos (ex: `finance.accounts.index`).
- Todas as rotas financeiras DEVEM ter middleware `auth`.

## Exceções de Domínio

- Erros de negócio DEVEM lançar exceções específicas (ex: `PeriodAlreadyExistsException`).
- Exceções DEVEM ser tratadas no Controller com mensagens amigáveis ao usuário.
- Exceções de domínio ficam em `app/Domain/{Entity}/Exceptions/`.

## Filtros e Paginação

- Cada listagem DEVE suportar filtros e paginação no backend.
- Eager loading DEVE ser usado para evitar N+1 em listagens com relacionamentos.
- Formato de resposta paginada: dados da página, total de registros, página atual, total de páginas.
