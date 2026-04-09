# Arquitetura — Himel App

> Este arquivo define a arquitetura e estrutura de pastas do projeto.

## Arquitetura Geral

O projeto segue Domain-Driven Design (DDD) simplificado no backend e arquitetura modular no frontend, conectados via Inertia.js (sem API REST separada para o frontend).

```
Usuário → Vue 3 (Inertia) → Laravel Controller → Service Layer → Eloquent → MySQL
```

## Estrutura Backend (Domain-Driven)

```
app/Domain/{Entity}/
├── Contracts/          # Interfaces de Services
├── Controllers/        # PageController (Inertia) + Controller (API)
├── Exceptions/         # Exceções de domínio
├── Listeners/          # Event listeners
├── Models/             # Eloquent Models
├── Policies/           # Authorization Policies
├── Requests/           # FormRequests (validação)
├── Resources/          # API Resources (serialização)
├── Routes/             # web.php e api.php do domínio
└── Services/           # Lógica de negócio
```

### Entidades de Domínio

- `Account` — Contas financeiras
- `Auth` — Autenticação (Fortify)
- `Category` — Categorias financeiras
- `CreditCard` — Cartões de crédito e compras/parcelas
- `FixedExpense` — Despesas fixas recorrentes
- `Period` — Períodos mensais (eixo central de organização)
- `Transaction` — Transações financeiras
- `Transfer` — Transferências entre contas

### Padrão de Controllers

Cada entidade possui dois controllers:
- `{Entity}PageController` — Renderiza páginas Inertia (index, show)
- `{Entity}Controller` — Operações CRUD via Inertia (store, update, destroy)

## Estrutura Frontend (Modular)

```
resources/js/
├── components/             # Componentes globais reutilizáveis
│   ├── ui/                 # Shadcn/Vue components
│   ├── layouts/            # AppLayout, AppSidebar, etc.
│   ├── PageHeader.vue      # Cabeçalho de páginas Index
│   ├── DeleteConfirmPopover.vue
│   ├── ValidatedInertiaForm.vue
│   └── ValidatedField.vue
├── modules/finance/        # Módulo financeiro
│   ├── components/         # Componentes do módulo (DataTable, FilterBar, Forms)
│   ├── composables/        # Hooks reutilizáveis (useFinanceFilters, usePagination, useCrudToast)
│   ├── services/           # Serviços (formatCurrency, formatDate)
│   ├── stores/             # Pinia stores por entidade
│   ├── types/              # TypeScript types (finance.ts)
│   └── validations/        # Zod schemas por entidade
├── pages/finance/          # Páginas Inertia por entidade
│   ├── accounts/Index.vue
│   ├── categories/Index.vue
│   ├── credit-cards/Index.vue
│   ├── periods/Index.vue
│   ├── periods/Show.vue
│   ├── transactions/Index.vue
│   └── transfers/Index.vue
└── actions/                # Wayfinder (auto-gerado)
```

## Padrão CRUD Frontend (Modal-Based)

Todas as operações CRUD são centralizadas na página Index de cada módulo via modais:
- Criação e edição via `ModalDialog` com formulário reutilizável
- Exclusão via `DeleteConfirmPopover` inline na tabela
- Estado gerenciado por Pinia store dedicado por módulo
- Sem páginas Create/Edit separadas

## Fluxo de Dados

```
1. Usuário interage com Vue component
2. Pinia store gerencia estado de UI (modal, item atual)
3. Formulário valida com Vee-Validate + Zod
4. Inertia router envia request ao Laravel
5. Controller delega ao Service (via Interface)
6. Service executa lógica + DB::transaction
7. Controller retorna redirect com flash message
8. Inertia recarrega props automaticamente
9. Toast exibido via useCrudToast
```
