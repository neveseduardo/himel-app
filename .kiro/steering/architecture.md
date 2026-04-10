---
inclusion: auto
priority: 40
---

# Arquitetura — Himel App

> Estrutura de pastas e padrões arquiteturais do projeto.

## Arquitetura Geral

Domain-Driven Design (DDD) simplificado no backend + arquitetura modular no frontend, conectados via Inertia.js.

```
Usuário → Vue 3 (Inertia) → Laravel Controller → Service Layer → Eloquent → MySQL
```

- NÃO existe API REST separada para o frontend SPA.
- Toda comunicação frontend ↔ backend é via Inertia.js.

## Estrutura Backend (Domain-Driven)

```
app/Domain/{Entity}/
├── Contracts/          # Interfaces de Services
├── Controllers/        # PageController (Inertia) + Controller (CRUD)
├── Exceptions/         # Exceções de domínio
├── Listeners/          # Event listeners
├── Models/             # Eloquent Models
├── Policies/           # Authorization Policies
├── Requests/           # FormRequests (validação)
├── Resources/          # API Resources (serialização)
├── Routes/             # web.php do domínio
└── Services/           # Lógica de negócio (DONO das regras)
```

### Entidades de Domínio

| Entidade | Responsabilidade |
|----------|-----------------|
| `Account` | Contas financeiras |
| `Auth` | Autenticação (Fortify) |
| `Category` | Categorias financeiras (INFLOW/OUTFLOW) |
| `CreditCard` | Cartões de crédito |
| `CreditCardCharge` | Compras no cartão |
| `CreditCardInstallment` | Parcelas de compras |
| `FixedExpense` | Despesas fixas recorrentes |
| `Period` | Períodos mensais (eixo central) |
| `Transaction` | Transações financeiras |
| `Transfer` | Transferências entre contas |

### Padrão de Controllers

Cada entidade possui dois controllers:
- `{Entity}PageController` — Renderiza páginas Inertia (index, show)
- `{Entity}Controller` — Operações CRUD via Inertia (store, update, destroy)

## Estrutura Frontend (Modular)

```
resources/js/
├── components/             # Componentes globais reutilizáveis
│   ├── ui/                 # Shadcn/Vue components
│   ├── layouts/            # AppLayout, AppSidebar
│   ├── PageHeader.vue
│   ├── DeleteConfirmPopover.vue
│   ├── ValidatedInertiaForm.vue
│   └── ValidatedField.vue
├── modules/finance/        # Módulo financeiro
│   ├── components/         # Forms, DataTable, FilterBar
│   ├── composables/        # useFinanceFilters, usePagination, useCrudToast
│   ├── services/           # formatCurrency, formatDate
│   ├── stores/             # Pinia stores por entidade
│   ├── types/              # TypeScript types
│   └── validations/        # Zod schemas por entidade
├── pages/finance/          # Páginas Inertia por entidade
└── actions/                # Wayfinder (auto-gerado)
```

## Fluxo de Dados

```
1. Usuário interage com Vue component
2. Pinia store gerencia estado de UI (modal, item atual)
3. Formulário valida com Vee-Validate + Zod (UX only)
4. Inertia router envia request ao Laravel
5. FormRequest valida no backend (segurança)
6. Controller delega ao Service (via Interface)
7. Service executa lógica + DB::transaction
8. Controller retorna redirect com flash message
9. Inertia recarrega props automaticamente
10. Toast exibido via useCrudToast
```

## Regras Estruturais

- NUNCA criar novas pastas raiz sem aprovação do usuário.
- Novas entidades DEVEM seguir a mesma estrutura DDD existente.
- NUNCA criar páginas Create/Edit separadas — usar modais na Index.
