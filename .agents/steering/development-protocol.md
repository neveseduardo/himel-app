# Protocolo de Desenvolvimento — SaaS Financeiro Himel App

> **Glob:** `app/**/*.php, resources/js/**/*.{ts,vue}`
>
> Este arquivo define as regras de infraestrutura e padrões de desenvolvimento. O agente DEVE seguir estas regras em toda implementação.

## Stack Tecnológica

| Camada | Tecnologia |
|--------|-----------|
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | Vue 3 (`<script setup lang="ts">`) |
| Comunicação | Inertia.js (bridge única BE ↔ FE) |
| Database | MySQL 8.0 |
| Estilização | Tailwind CSS 4 + Shadcn/Vue |
| Qualidade | ESLint 9 + Laravel Boost |
| Rotas FE | Wayfinder (gerador de rotas tipadas) |

## Regras de Backend

### Identificadores (UUID v4)

- É PROIBIDO usar IDs incrementais (`int`). Todos os models (incluindo `User`) DEVEM usar UUID v4.
- Foreign keys DEVEM seguir o padrão `{model}_uid` (ex: `user_uid`).
- Toda Model DEVE definir:
  ```php
  protected $primaryKey = 'uid';
  public $incrementing = false;
  protected $keyType = 'string';
  ```
- DEVE usar a trait `HasUids` para geração automática de UUID.

### Service Layer

- Controllers NÃO DEVEM conter lógica de negócio. Toda regra DEVE residir em `App\Services`.
- Toda Service DEVE implementar uma Interface.
- O Controller DEVE injetar a Interface da Service no construtor.

### Controllers e Segurança

- Operações de escrita (`POST`, `PUT`, `DELETE`) DEVEM ter `try-catch` no Controller.
- O `DB::transaction` DEVE existir APENAS no Service Layer (NUNCA no Controller).
- O bloco `catch` DEVE registrar logs via `Log::error()` com contexto detalhado.
- Requests DEVEM utilizar `FormRequests` com mensagens de erro em Português (pt-BR).

### Transacionalidade

- O `DB::transaction` é responsabilidade EXCLUSIVA do Service Layer.
- Controllers delegam diretamente ao Service sem encapsular em transação.
- Isso evita transações aninhadas desnecessárias.

## Regras de Frontend

### Componentização e Design

- USAR EXCLUSIVAMENTE componentes Shadcn/Vue existentes no projeto. NÃO criar componentes de UI básicos se houver equivalente no Shadcn.
- Caso não exista, criar em `components/ui` ou adicionar via: `npx shadcn-vue@latest add <componente>`.
- É PROIBIDO usar `any` em TypeScript. Todo código Vue DEVE ser tipado.

### Arquitetura Modular

Toda lógica de frontend DEVE seguir a estrutura em `resources/js/Modules/`:

| Pasta | Conteúdo |
|-------|----------|
| `store/` | Estado (Pinia) |
| `components/` | Componentes específicos do módulo |
| `services/` | Serviços de frontend (`*.services.ts`) |
| `composables/` | Hooks reutilizáveis |

### Roteamento e Formulários

- USAR EXCLUSIVAMENTE o Wayfinder para chamadas de rotas. URLs em string pura são PROIBIDAS.
- Navegação via componente `<Link>` do Inertia.
- Formulários DEVEM usar validação com Vee-Validate + Zod.
- Submissão via `useForm` do Inertia.

## Checklist de Validação (Definition of Done)

Antes de entregar qualquer alteração, o agente DEVE executar e validar:

1. `npm run lint` — sem erros
2. `npx vue-tsc --noEmit` — sem erros de tipo
3. `npm run build` — build bem-sucedido

## Diretrizes Anti-Alucinação

- VERIFICAR compatibilidade com Tailwind CSS 4 (NÃO usar `tailwind.config.js` se a v4 for via CSS).
- NUNCA ignorar um erro no backend. SEMPRE retornar resposta via Inertia com mensagem de erro tratada.
- VERIFICAR se componentes Shadcn/Vue referenciados existem no projeto antes de usá-los.

## Mapeamento de Models e Relacionamentos

### Models Obrigatórias

| Model | PK | Enums | Relacionamentos |
|-------|-----|-------|-----------------|
| `FinancialAccount` | `uid` | `type`: CHECKING, SAVINGS, CASH, OTHER | HasMany Transaction, HasMany Transfer |
| `FinancialCategory` | `uid` | `direction`: INFLOW, OUTFLOW | HasMany Transaction, HasMany FixedExpense |
| `FinancialTransaction` | `uid` | `direction`: INFLOW, OUTFLOW; `source`: MANUAL, CREDIT_CARD, FIXED, TRANSFER; `status`: PENDING, PAID, OVERDUE | BelongsTo Account (`financial_account_uid`), BelongsTo Category (`financial_category_uid`) |
| `FinancialTransfer` | `uid` | — | BelongsTo Account (`from_account_uid`), BelongsTo Account (`to_account_uid`) |
| `FinancialFixedExpense` | `uid` | — | BelongsTo Category (`financial_category_uid`) |
| `FinancialCreditCard` | `uid` | `card_type`: PHYSICAL, VIRTUAL | HasMany CreditCardCharge |
| `FinancialCreditCardCharge` | `uid` | — | BelongsTo CreditCard (`credit_card_uid`), HasMany CreditCardInstallment |
| `FinancialCreditCardInstallment` | `uid` | — | BelongsTo CreditCardCharge (`credit_card_charge_uid`), BelongsTo Transaction (`financial_transaction_uid`) |
| `FinancialPeriod` | `uid` | — | Unique: `(user_uid, month, year)` |

### Regra de Implementação Eloquent

Toda Model DEVE incluir:

```php
protected $primaryKey = 'uid';
public $incrementing = false;
protected $keyType = 'string';
// + trait HasUids para geração automática de UUID
```

## Filtros e Paginação

- Cada model DEVE ter suporte a filtros e paginação no backend.
- Filtros DEVEM ser implementados no backend e expostos via API para consumo do frontend.
- Formato de resposta paginada DEVE incluir: dados da página, total de registros, página atual, total de páginas, filtros aplicados.
- Frontend DEVE implementar: lista de dados + componentes de filtro + controles de paginação.
