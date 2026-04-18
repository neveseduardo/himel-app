---
inclusion: always
---

# Convenções e Padrões

## Stack

- Laravel 13 (PHP 8.4) / Inertia.js v3 / Vue 3 Composition API (`<script setup lang="ts">`)
- Pinia 3 / Tailwind CSS 4
- Vee-Validate + Zod / Laravel Fortify (auth)
- Laravel Sanctum (API tokens) / Laravel Wayfinder (typed routes)
- PHPUnit 12 / ESLint 9 / Prettier 3
- Playwright (E2E testing, headless Chromium)
- reka-ui, lucide-vue-next, vue-sonner, @tanstack/vue-table

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
├── composables/           # Composables de infraestrutura (useAppearance, useCurrentUrl, useInitials)
├── domain/                # Domínios DDD do frontend
│   ├── Account/           # Contas bancárias
│   ├── Auth/              # Autenticação e 2FA
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
├── lib/                   # Utilitários (utils.ts)
├── pages/                 # Páginas Inertia (file-based routing)
├── routes/                # Wayfinder: typed named routes
├── types/                 # TypeScript types/interfaces globais
└── app.ts                 # Entry point

routes/                    # Definição de rotas Laravel
tests/                     # PHPUnit tests (Feature + Unit)
database/                  # Migrations, factories, seeders
```

## Estrutura de Domínio (DDD)

Cada domínio em `app/Domain/<Domínio>/` segue:

```
├── Contracts/             # Interfaces de serviço
├── Controllers/           # Controllers do domínio
├── Models/                # Eloquent Models
├── Policies/              # Authorization Policies
├── Requests/              # Form Requests (Store/Update)
├── Resources/             # API Resources
├── Routes/                # Rotas do domínio
└── Services/              # Service classes
```

### Frontend — Domínio DDD

Cada domínio em `resources/js/domain/<Domínio>/` segue:

```
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

## Formatação

### PHP (Pint)
- Seguir configuração do `pint.json`
- Rodar `vendor/bin/pint --dirty --format agent` após alterações

### JS/Vue (ESLint + Prettier)
- Seguir configuração do `eslint.config.js`
- Rodar `npm run lint` após alterações

## Imports (Frontend)

- `@/` → `resources/js/`
- Wayfinder actions: `import { ... } from '@/actions/...'`
- Wayfinder routes: `import { ... } from '@/routes/...'`
- Auto-imports via `unplugin-auto-import` e `unplugin-vue-components`

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
