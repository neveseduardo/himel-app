# Regras de Negócio Financeiras — Himel App

> **Glob:** `app/Domain/**/*.php`
>
> Regras de negócio obrigatórias do domínio financeiro. Consultar ANTES de implementar qualquer funcionalidade.

## Princípios

- NÃO existe fechamento de mês manual — o sistema opera como fluxo contínuo.
- O conceito central baseia-se em movimentações vinculadas a contas e organizadas por períodos.
- Valores monetários (`amount`) DEVEM ser sempre decimais positivos. A natureza é definida por `direction`.

## Onboarding

- O usuário NÃO PODE registrar transações ou despesas fixas sem ao menos uma `FinancialAccount`.
- No primeiro acesso, criar automaticamente categorias padrão:
  - **OUTFLOW:** Alimentação, Moradia, Transporte, Saúde, Educação, Lazer, Vestuário, Outros
  - **INFLOW:** Salário, Freelance, Investimentos, Outros

## Transações

### Criação
- Campos obrigatórios: `amount`, `financial_account_uid`, `financial_category_uid`, `occurred_at`, `direction`.
- PROIBIDO vincular categoria INFLOW a transação OUTFLOW (e vice-versa).
- Campo opcional `period_uid` para vincular transação a um período.

### Ciclo de Vida (Status)

| Status | Condição |
|--------|----------|
| `PENDING` | Transação com `due_date` futuro |
| `PAID` | Usuário confirma pagamento → preencher `paid_at` |
| `OVERDUE` | Automatizado: `PENDING` onde `due_date < now()` |

### Cálculo de Saldo
- Saldo da conta atualizado APENAS por transações com status `PAID`.
- Transações `PENDING` ou `OVERDUE` NÃO afetam o saldo.

## Cartão de Crédito

- **Charge (Compra):** Registro pai com valor total e número de parcelas.
- **Installments:** Sistema gera automaticamente N parcelas.
- Cada parcela gera uma `FinancialTransaction` vinculada via `reference_id`.
- `due_date` calculado a partir do `due_day` do cartão e mês de cada parcela.
- Parcelas: entre 1 e 48.
- Centavos residuais da divisão distribuídos na última parcela.

## Despesas Fixas

- Projetar transação para o mês atual/seguinte baseando-se no `due_day`.
- Podem ser pausadas via `active: false`.
- Na inicialização de período, geram transações com `source=FIXED`.

## Transferências

- Operação atômica: débito na conta de origem + crédito na conta de destino.
- DEVE ser tratada via `DB::transaction`.

## Períodos

- Períodos são criados explicitamente pelo usuário (mês/ano).
- Unique constraint: `(user_uid, month, year)`.
- Inicialização de período carrega automaticamente:
  - Despesas fixas ativas → transações `PENDING` com `source=FIXED`
  - Parcelas de cartão pendentes do mês → vincula ou cria transações com `source=CREDIT_CARD`
- Inicialização DEVE ser idempotente (re-execução segura sem duplicatas).
- Exclusão de período bloqueada se houver transações `PAID` vinculadas.
- Exclusão permitida desvincula transações `PENDING`/`OVERDUE` (define `period_uid=null`).

## Restrições de Edição

- Transações geradas por "Cartão de Crédito" ou "Transferências" NÃO PODEM ter valor alterado isoladamente.
- Alteração DEVE ser feita na entidade pai (Parcela ou Transferência).

## Enums

| Entidade | Campo | Valores |
|----------|-------|---------|
| Account | `type` | `CHECKING`, `SAVINGS`, `CASH`, `OTHER` |
| Category | `direction` | `INFLOW`, `OUTFLOW` |
| Transaction | `status` | `PENDING`, `PAID`, `OVERDUE` |
| Transaction | `source` | `MANUAL`, `CREDIT_CARD`, `FIXED`, `TRANSFER` |
| CreditCard | `card_type` | `PHYSICAL`, `VIRTUAL` |
