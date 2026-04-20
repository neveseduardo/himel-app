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

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain.

- `laravel-best-practices` — Apply when writing, reviewing, or refactoring Laravel PHP code (controllers, models, migrations, form requests, policies, services, Eloquent queries, N+1, caching, authorization, validation, error handling, queue/job config, route definitions, architectural decisions).
- `wayfinder-development` — Use when frontend code needs to call backend routes or controller actions. Trigger when: connecting Vue/Inertia frontend to Laravel controllers, building end-to-end features, wiring up forms/links to backend endpoints, fixing route-related TypeScript errors, importing from @/actions or @/routes, or running wayfinder:generate.
- `inertia-vue-development` — Activates when creating Vue pages, forms, or navigation; using `<Link>`, `<Form>`, `useForm`, or `router`; working with deferred props, prefetching, or polling.
- `tailwindcss-development` — Invoke for: responsive grid layouts, flex/grid page structures, styling UI components, dark mode variants, spacing/typography, Tailwind v4 work. Skip for backend PHP logic, database queries, API routes.
- `fortify-development` — ACTIVATE when working on authentication: login, registration, password reset, email verification, 2FA/TOTP/QR codes/recovery codes, profile updates, password confirmation, auth-related routes/controllers.

## Laravel Boost Tools

- Use `database-query` to run read-only queries against the database
- Use `database-schema` to inspect table structure before writing migrations or models
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs
- Use `browser-logs` to read browser logs, errors, and exceptions
- Always use `search-docs` before making code changes (version-specific docs)

### Search Docs Syntax

1. Words for auto-stemmed AND logic: `rate limit`
2. `"quoted phrases"` for exact position matching: `"infinite scroll"`
3. Combine words and phrases: `middleware "rate limit"`
4. Multiple queries for OR logic: `queries=["authentication", "middleware"]`
5. Do not add package names to queries — package info is already shared

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
