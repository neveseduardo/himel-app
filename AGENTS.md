# Himel App — Agent Guidelines

## Sobre o Projeto

Aplicação de gestão financeira pessoal construída com Laravel 13 + Inertia.js v3 + Vue 3. Arquitetura Domain-Driven Design (DDD) com domínios: Account, Category, CreditCard, CreditCardCharge, CreditCardInstallment, FixedExpense, Period, Settings, Transaction, Transfer, User. Backend PHP 8.4 com API REST, frontend SPA via Inertia/Vue com Pinia, Tailwind CSS 4, Vee-Validate + Zod.

---

## Stack & Versões

- php - 8.4
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v3
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v13
- laravel/sanctum (SANCTUM) - v4
- laravel/wayfinder (WAYFINDER) - v0
- laravel/boost (BOOST) - v2
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v12
- @inertiajs/vue3 (INERTIA_VUE) - v2
- tailwindcss (TAILWINDCSS) - v4
- vue (VUE) - v3
- @laravel/vite-plugin-wayfinder (WAYFINDER_VITE) - v0
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3
- Pinia 3 / Vee-Validate + Zod
- Playwright (E2E testing, headless Chromium)
- reka-ui, lucide-vue-next, vue-sonner, @tanstack/vue-table

---

## Estrutura de Diretórios

```
app/
├── Domain/                # Domínios DDD
│   ├── Account/           # Contas bancárias
│   ├── Category/          # Categorias de transação
│   ├── CreditCard/        # Cartões de crédito
│   ├── CreditCardCharge/  # Cobranças de cartão
│   ├── CreditCardInstallment/ # Parcelas de cartão
│   ├── FixedExpense/      # Despesas fixas
│   ├── Period/            # Períodos financeiros
│   ├── Settings/          # Configurações
│   ├── Shared/            # Código compartilhado entre domínios
│   ├── Transaction/       # Transações
│   ├── Transfer/          # Transferências
│   └── User/              # Usuários
├── Http/                  # Controllers globais, Middleware
└── Providers/             # Service Providers

resources/js/
├── actions/               # Wayfinder: typed controller actions
├── components/            # Componentes Vue reutilizáveis (layout + UI base)
├── composables/           # Composables de infraestrutura
├── domain/                # Domínios DDD do frontend
│   ├── Account/
│   ├── Auth/
│   ├── Category/
│   ├── CreditCard/
│   ├── CreditCardCharge/
│   ├── CreditCardInstallment/
│   ├── FixedExpense/
│   ├── Period/
│   ├── Settings/
│   ├── Shared/
│   ├── Transaction/
│   ├── Transfer/
│   └── User/
├── lib/                   # Utilitários (utils.ts)
├── pages/                 # Páginas Inertia (file-based routing)
├── routes/                # Wayfinder: typed named routes
├── types/                 # TypeScript types/interfaces globais
└── app.ts                 # Entry point

routes/                    # Definição de rotas Laravel
tests/                     # PHPUnit tests (Feature + Unit)
database/                  # Migrations, factories, seeders

e2e/
├── .auth/                 # Sessão autenticada (gitignored)
├── pages/                 # Page Objects por módulo
├── results/               # Traces e resultados (gitignored)
├── setup/global-setup.ts  # Auth + seeding antes da suite
├── tests/                 # Specs por módulo
└── start-server.sh        # Script para CI/CD
```

### Estrutura de Domínio Backend

```
app/Domain/<Domínio>/
├── Contracts/             # Interfaces de serviço
├── Controllers/           # Controllers do domínio
├── Models/                # Eloquent Models
├── Policies/              # Authorization Policies
├── Requests/              # Form Requests (Store/Update)
├── Resources/             # API Resources
├── Routes/                # Rotas do domínio
└── Services/              # Service classes
```

### Estrutura de Domínio Frontend

```
resources/js/domain/<Domínio>/
├── components/            # Componentes Vue do domínio
├── composables/           # Composables (casos de uso)
├── services/              # Ports (interfaces TypeScript)
│   └── adapters/          # Adapters (implementações concretas)
├── stores/                # Pinia stores
├── types/                 # Interfaces e tipos TypeScript
└── validations/           # Schemas Zod
```

Regra de Dependência entre camadas:
- `types/` → nenhum import do domínio
- `validations/` → `types/`
- `stores/` → `types/`
- `services/` (ports) → `types/`
- `composables/` → `types/`, `stores/`, `services/` (ports)
- `components/` → `composables/`, `stores/`, `types/`
- `adapters/` → `services/` (ports), bibliotecas externas

---

## Nomenclatura

| Artefato | Convenção | Exemplo |
|---|---|---|
| Controllers | PascalCase + Controller | `AccountController.php` |
| Models | PascalCase singular | `Account.php` |
| Form Requests | Store/Update + PascalCase | `StoreAccountRequest.php` |
| Policies | PascalCase + Policy | `AccountPolicy.php` |
| Resources | PascalCase + Resource | `AccountResource.php` |
| Services | PascalCase + Service | `AccountService.php` |
| Migrations | snake_case timestamp | `2024_01_01_create_accounts_table.php` |
| Vue Components | PascalCase | `AccountForm.vue` |
| Composables | camelCase com `use` | `useAccounts.ts` |
| Stores (Pinia) | camelCase | `accounts.ts` → `useAccountsStore` |
| Types/Interfaces | PascalCase | `interface Account {}` |
| Testes | PascalCase + Test | `AccountTest.php` |

---

## Workflow Git

- Branch principal: `develop` — toda branch nasce dela e volta pra ela
- Padrão de commit: Conventional Commits (`tipo: Mensagem descritiva`)
- Tipos válidos: `feat`, `fix`, `refactor`, `chore`, `docs`, `style`, `test`
- Commits atômicos: uma mudança lógica por commit
- Push e PR sempre para `develop`

---

## Regras Globais

### Proteção contra Sobrescrita

- MUST ler o arquivo COMPLETO antes de alterar qualquer componente
- MUST identificar TODAS as funcionalidades existentes
- MUST usar `strReplace` para alterações pontuais — evitar `fsWrite` em arquivos grandes
- MUST NOT remover ou substituir funcionalidades existentes sem autorização
- MUST preservar imports, props, emits e lógica de negócio ao refatorar

### Tratamento de Bugs

- Bug no escopo da tarefa atual → corrigir na mesma branch como `fix: Descrição`
- Bug fora do escopo → parar, informar o usuário, perguntar como proceder
- Bug pré-existente → não misturar com a tarefa atual, sugerir issue separada

### Checklist Pré-Push

- `vendor/bin/pint --dirty --format agent` sem erros (PHP)
- `npm run lint` sem erros (JS/Vue)
- `npm run types:check` sem erros (TypeScript)
- `php artisan test --compact` sem falhas
- Commits seguem Conventional Commits
- Sem `console.log`, `any` injustificado, imports desnecessários
- Branch atualizada com develop

---

## Backend Laravel (API + Domain)

### Padrão de Controller

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

### Regras Backend

- MUST usar Form Requests para validação (Store/Update)
- MUST usar Policies para autorização
- MUST usar API Resources para respostas
- MUST usar Service classes para lógica de negócio
- MUST usar Contracts (interfaces) para services
- MUST registrar bindings no AppServiceProvider
- MUST usar constructor property promotion (PHP 8.4)
- MUST usar return types explícitos em todos os métodos
- MUST rodar `vendor/bin/pint --dirty --format agent` após alterações PHP
- Use curly braces para control structures, mesmo single-line
- PHPDoc blocks sobre inline comments
- Array shape type definitions em PHPDoc blocks
- TitleCase para Enum keys

### Rotas

- Definidas em `app/Domain/<Domínio>/Routes/` ou `routes/`
- Usar named routes: `Route::name('accounts.index')`
- Wayfinder gera typed functions automaticamente para o frontend

### Artisan

- Usar `php artisan make:` para criar arquivos (migrations, controllers, models, etc.)
- Passar `--no-interaction` a todos os comandos Artisan
- Inspecionar rotas: `php artisan route:list`
- Configuração: `php artisan config:show app.name`

---

## Frontend (Inertia + Vue + Pinia)

### Componentes Vue — Estrutura

```vue
<script setup lang="ts">
<!-- lógica -->
</script>

<template>
<!-- markup -->
</template>
```

### Ordem no `<script setup>`

1. Imports (bibliotecas externas → componentes locais → tipos)
2. Props e Emits (`defineProps<T>()`, `defineEmits<T>()`)
3. Estado reativo (`ref`, `reactive`)
4. Computed properties
5. Watchers
6. Funções/handlers
7. Lifecycle hooks (`onMounted`, etc.)

### Inertia.js v3

- Páginas em `resources/js/pages/`
- Usar `router` do Inertia para navegação (não `window.location`)
- Usar `useForm` para formulários
- Usar Wayfinder para URLs tipadas (`@/actions/`, `@/routes/`)
- Deferred props: adicionar skeleton/loading state
- `Inertia::optional()` em vez de `Inertia::lazy()`
- Novidades v3: `useHttp`, optimistic updates, `useLayoutProps`, instant visits
- Axios removido — usar built-in XHR client
- Event renames: `invalid` → `httpException`, `exception` → `networkError`
- `router.cancelAll()` substitui `router.cancel()`

### Stores (Pinia)

```ts
export const useAccountsStore = defineStore('accounts', () => {
    const items = ref<Account[]>([])
    const loading = ref(false)

    async function fetchItems() {
        loading.value = true
        try {
            // usar Inertia router ou Wayfinder
        } finally {
            loading.value = false
        }
    }

    return { items, loading, fetchItems }
})
```

- MUST usar setup syntax (`defineStore('name', () => { ... })`)
- MUST usar try/catch/finally em toda operação async
- MUST retornar explicitamente todas as propriedades e métodos públicos

### Validação de Formulários

- Schemas Zod em `resources/js/domain/<Domínio>/validations/`
- Integrar via `toTypedSchema()` do `@vee-validate/zod`

### UI Components

- reka-ui para componentes headless
- lucide-vue-next para ícones
- vue-sonner para toasts/notificações
- @tanstack/vue-table para tabelas
- class-variance-authority + tailwind-merge para variantes de estilo

### Regras de Template

- Componentes: PascalCase (`<AccountForm />`)
- Props: camelCase (`:accountId`)
- Eventos: kebab-case (`@account-created`)
- `v-model` ao invés de prop + emit manual quando possível

### Imports

- `@/` → `resources/js/`
- Wayfinder actions: `import { ... } from '@/actions/...'`
- Wayfinder routes: `import { ... } from '@/routes/...'`
- Auto-imports via `unplugin-auto-import` e `unplugin-vue-components`

---

## Padrões Proibidos

### PHP
- MUST NOT usar `any` sem justificativa em PHPDoc
- MUST NOT criar rotas fora dos arquivos de rotas do domínio
- MUST NOT fazer queries direto no controller — usar Service
- MUST NOT instalar/remover dependências sem autorização do usuário

### Frontend
- MUST NOT usar Options API, `defineComponent()`, `this`
- MUST NOT usar `var` — usar `const` ou `let`
- MUST NOT usar `console.log` em produção
- MUST NOT usar `axios` ou `fetch` nativo — usar Inertia router ou Wayfinder
- MUST NOT usar `any` sem justificativa — usar tipos corretos ou `unknown`
- MUST NOT fazer chamadas de API direto no componente — centralizar na store ou usar Inertia

---

## Testes

### PHPUnit

- Todos os testes MUST ser PHPUnit classes (não Pest)
- Usar factories para criar models em testes
- Testar happy paths, failure paths e edge cases
- Rodar: `php artisan test --compact --filter=NomeDoTeste`
- Rodar suite completa: `php artisan test --compact`
- MUST NOT remover testes sem aprovação

### Testes E2E (Playwright)

#### Execução

- `npm run test:e2e` — roda todos os testes (sobe servidor automaticamente)
- `npm run test:e2e:ui` — modo interativo para debug
- `npx playwright test credit-card.spec.ts` — roda um arquivo específico

#### Page Objects

- Um Page Object por módulo em `e2e/pages/<Modulo>Page.ts`
- Usar `locator('[name="field"]')` para inputs de formulário
- Usar `getByRole()` para botões, headings, dialogs
- Usar `getByText()` para conteúdo textual
- NUNCA usar `waitForLoadState('networkidle')` — trava com Vite HMR

#### Convenções E2E

- Organizar por `test.describe`: Listing, Search, Pagination, Creation, Editing, Viewing, Deletion
- Testes read-only antes de testes de mutação
- Timeout de teste: 15s. Timeout de ação: 5s.
- Seeder DEVE limpar dados anteriores antes de re-seed

#### Armadilhas Conhecidas

- `getByLabel()` não funciona se input não tem `id` correspondente ao `for` do label
- `InputPassword` é wrapper `<div>` — `<input>` real está dentro
- Arquivo `public/hot` faz Laravel ignorar o build
- `app.blade.php` NÃO deve referenciar páginas individuais no `@vite()`

#### Novo Módulo E2E — Checklist

1. Criar Page Object em `e2e/pages/<Modulo>Page.ts`
2. Criar spec em `e2e/tests/<modulo>.spec.ts`
3. Atualizar `E2eTestSeeder.php` com dados do novo módulo (com reset)
4. Rodar `npm run test:e2e` e iterar

---

## Formatação

### PHP (Pint)
- Seguir configuração do `pint.json`
- Rodar `vendor/bin/pint --dirty --format agent` após alterações

### JS/Vue (ESLint + Prettier)
- Seguir configuração do `eslint.config.js`
- Rodar `npm run lint` após alterações

---

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v3
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- laravel/wayfinder (WAYFINDER) - v0
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v12
- @inertiajs/vue3 (INERTIA_VUE) - v2
- tailwindcss (TAILWINDCSS) - v4
- vue (VUE) - v3
- @laravel/vite-plugin-wayfinder (WAYFINDER_VITE) - v0
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `laravel-best-practices` — Apply this skill whenever writing, reviewing, or refactoring Laravel PHP code. This includes creating or modifying controllers, models, migrations, form requests, policies, jobs, scheduled commands, service classes, and Eloquent queries. Triggers for N+1 and query performance issues, caching strategies, authorization and security patterns, validation, error handling, queue and job configuration, route definitions, and architectural decisions. Also use for Laravel code reviews and refactoring existing Laravel code to follow best practices. Covers any task involving Laravel backend PHP code patterns.
- `wayfinder-development` — Use this skill for Laravel Wayfinder which auto-generates typed functions for Laravel controllers and routes. ALWAYS use this skill when frontend code needs to call backend routes or controller actions. Trigger when: connecting any React/Vue/Svelte/Inertia frontend to Laravel controllers, routes, building end-to-end features with both frontend and backend, wiring up forms or links to backend endpoints, fixing route-related TypeScript errors, importing from @/actions or @/routes, or running wayfinder:generate. Use Wayfinder route functions instead of hardcoded URLs. Covers: wayfinder() vite plugin, .url()/.get()/.post()/.form(), query params, route model binding, tree-shaking. Do not use for backend-only task
- `inertia-vue-development` — Develops Inertia.js v2 Vue client-side applications. Activates when creating Vue pages, forms, or navigation; using <Link>, <Form>, useForm, or router; working with deferred props, prefetching, or polling; or when user mentions Vue with Inertia, Vue pages, Vue forms, or Vue navigation.
- `tailwindcss-development` — Always invoke when the user's message includes 'tailwind' in any form. Also invoke for: building responsive grid layouts (multi-column card grids, product grids), flex/grid page structures (dashboards with sidebars, fixed topbars, mobile-toggle navs), styling UI components (cards, tables, navbars, pricing sections, forms, inputs, badges), adding dark mode variants, fixing spacing or typography, and Tailwind v3/v4 work. The core use case: writing or fixing Tailwind utility classes in HTML templates (Blade, JSX, Vue). Skip for backend PHP logic, database queries, API routes, JavaScript with no HTML/CSS component, CSS file audits, build tool configuration, and vanilla CSS.
- `fortify-development` — ACTIVATE when the user works on authentication in Laravel. This includes login, registration, password reset, email verification, two-factor authentication (2FA/TOTP/QR codes/recovery codes), profile updates, password confirmation, or any auth-related routes and controllers. Activate when the user mentions Fortify, auth, authentication, login, register, signup, forgot password, verify email, 2FA, or references app/Actions/Fortify/, CreateNewUser, UpdateUserProfileInformation, FortifyServiceProvider, config/fortify.php, or auth guards. Fortify is the frontend-agnostic authentication backend for Laravel that registers all auth routes and controllers. Also activate when building SPA or headless authentication, customizing login redirects, overriding response contracts like LoginResponse, or configuring login throttling. Do NOT activate for Laravel Passport (OAuth2 API tokens), Socialite (OAuth social login), or non-auth Laravel features.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.
- To check environment variables, read the `.env` file directly.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== wayfinder/core rules ===

# Laravel Wayfinder

Use Wayfinder to generate TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

=== inertia-vue/core rules ===

# Inertia + Vue

Vue components must have a single root element.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

</laravel-boost-guidelines>

---

## Histórico de Desenvolvimento

### Spec: Frontend DDD Restructure

Migração do frontend de `resources/js/modules/` para `resources/js/domain/`, adotando Domain-Driven Design espelhando o backend.

**Decisões de Design:**
- Referências cruzadas entre domínios usam tipos inline simplificados
- Tipos compartilhados centralizados em `domain/Shared/types/`
- Ports são interfaces TypeScript puras sem dependências de infraestrutura
- Adapters implementam Ports e encapsulam Inertia/Wayfinder/fetch
- Composables recebem adapters via parâmetro (Inversão de Controle)

**Resultado:** 13 domínios criados com types, stores, validations, services (ports + adapters), composables e components migrados. Estrutura antiga `modules/` removida.

---

### Spec: E2E Testing CreditCard

Infraestrutura de testes E2E com Playwright para o módulo CreditCard, primeiro módulo de uma estratégia módulo-a-módulo.

**Decisões de Design:**
- Playwright sobre Cypress: melhor suporte a SPA/Inertia, TypeScript nativo, auto-waiting superior
- Global setup autentica uma vez e salva `storageState` para reuso
- Page Object centraliza selectors baseados em roles/text/placeholder
- Seeder idempotente com dados nomeados + factory para volume

**Resultado:** 26 testes E2E cobrindo CRUD completo de CreditCard. Infraestrutura pronta para expansão módulo-a-módulo.

**Bugs encontrados e corrigidos durante E2E:**
- `AppLayout.vue` e `AuthLayout.vue` com recursão infinita (referenciavam a si mesmos)
- `<Sonner />` não renderizado em nenhum layout
- `Input.vue` não sincronizava `value` prop do vee-validate
- `app.blade.php` referenciava páginas Vue individualmente no `@vite()`
- Colunas `closing_day` e `last_four_digits` não existiam no banco
- `FinancialCreditCardFactory` sem `protected $model` definido

---

### Spec: CreditCard Dialog Desync Fix

Correção do bug onde o dialog de criação/edição do CreditCard parava de abrir após fechamento via ESC ou overlay click. `ModalDialog.vue` emite `update:open` via watch, `Index.vue` escuta e sincroniza `store.closeModal()`. 2 testes E2E adicionados (28 total).

---

### Spec: E2E Testing CreditCardCharge

Testes E2E para o módulo CreditCardCharge. Page Object `CreditCardChargePage`, seeder com 3 compras nomeadas + 13 factory. 26 testes (19 ativos + 7 skipped para edição/exclusão não implementadas na UI).

---

### Spec: E2E Testing FixedExpense

Testes E2E para o módulo FixedExpense. Fix da factory `FinancialFixedExpenseFactory` ($model), dialog sync fix no Index.vue, Page Object com suporte a combobox e checkbox. 28 testes E2E cobrindo CRUD completo.

---

### Spec: Period Expenses & Installments

Enriquecimento da página Show do Period com despesas fixas, parcelas de cartão (numeração X/Y), breakdown por cartão e composição detalhada das saídas. 3 novos métodos no PeriodService, 6 novas interfaces TypeScript, 14 testes PHPUnit. Sem migrations — dados já existiam nas tabelas.

---

### Spec: Pages Header Standardization

Padronização do `PageHeader.vue`: API rígida (props `buttonLabel`/`buttonIcon` + emit `action`) substituída por API flexível com `title`, `breadcrumbs?`, slots `#back` e `#actions`. 8 Index pages + `periods/Show.vue` migradas.

---

### Spec: Transaction Income/Expense Split

Diferenciação de transações INFLOW/OUTFLOW em toda a stack. Backend: validação condicional via `required_if:direction,OUTFLOW`, `prepareForValidation()` com defaults para INFLOW, `InsufficientBalanceException`. Frontend: `InflowTransactionForm.vue` simplificado, dropdown "Nova Transação" com opções "Entrada"/"Saída", modais separados no store. Saldo: INFLOW credita imediatamente, OUTFLOW só debita quando PAID (com check de saldo). Sem migrations.
