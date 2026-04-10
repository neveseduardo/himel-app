---
inclusion: auto
priority: 100
---

# Regras de Negócio Financeiras — Himel App

> **Glob:** `app/Domain/**/*.php`
>
> Este arquivo tem PRIORIDADE MÁXIMA. Em caso de conflito com qualquer outro steering, estas regras PREVALECEM.
> Consultar ANTES de implementar qualquer funcionalidade financeira.

## Princípios Fundamentais

- O sistema opera como fluxo contínuo. NÃO existe fechamento de mês manual.
- Movimentações são vinculadas a contas e organizadas por períodos mensais.
- Valores monetários (`amount`) DEVEM ser sempre `DECIMAL(12,2)` positivos. A natureza é definida exclusivamente por `direction`.
- Toda lógica financeira DEVE residir no Service Layer. Controllers e frontend NUNCA DEVEM conter cálculos financeiros.

## Onboarding

- O usuário NÃO PODE registrar transações ou despesas fixas sem ao menos uma `FinancialAccount`.
- No primeiro acesso, o sistema DEVE criar automaticamente categorias padrão:
  - **OUTFLOW:** Alimentação, Moradia, Transporte, Saúde, Educação, Lazer, Vestuário, Outros
  - **INFLOW:** Salário, Freelance, Investimentos, Outros

## Transações

### Criação
- Campos obrigatórios: `amount`, `financial_account_uid`, `financial_category_uid`, `occurred_at`, `direction`.
- É PROIBIDO vincular categoria INFLOW a transação OUTFLOW (e vice-versa). O Service DEVE validar essa consistência.
- Campo opcional `period_uid` para vincular transação a um período.

### Ciclo de Vida (Status)

| Status | Condição | Impacto no Saldo |
|--------|----------|------------------|
| `PENDING` | Transação com `due_date` futuro | Nenhum |
| `PAID` | Usuário confirma pagamento → preencher `paid_at` | Atualiza saldo |
| `OVERDUE` | Automatizado: `PENDING` onde `due_date < now()` | Nenhum |

### Cálculo de Saldo
- Saldo da conta DEVE ser atualizado APENAS por transações com status `PAID`.
- Transações `PENDING` ou `OVERDUE` NUNCA DEVEM afetar o saldo.
- Atualização de saldo DEVE ocorrer dentro de `DB::transaction` no Service.

## Cartão de Crédito

- **Charge (Compra):** Registro pai com valor total e número de parcelas.
- **Installments:** Sistema DEVE gerar automaticamente N parcelas ao criar um Charge.
- Cada parcela DEVE gerar uma `FinancialTransaction` vinculada via `reference_id`.
- `due_date` DEVE ser calculado a partir do `due_day` do cartão e mês de cada parcela.
- Parcelas: mínimo 1, máximo 48.
- Centavos residuais da divisão DEVEM ser distribuídos na última parcela.

## Despesas Fixas

- Projetar transação para o mês atual/seguinte baseando-se no `due_day`.
- Podem ser pausadas via `active: false`.
- Na inicialização de período, DEVEM gerar transações com `source=FIXED`.

## Transferências

- Operação atômica: débito na conta de origem + crédito na conta de destino.
- DEVE ser tratada via `DB::transaction` no Service Layer.
- Contas de origem e destino DEVEM ser diferentes.

## Períodos

- Períodos são criados explicitamente pelo usuário (mês/ano).
- Unique constraint: `(user_uid, month, year)`. Tentativa de duplicata DEVE retornar 409.
- Inicialização de período DEVE carregar automaticamente:
  - Despesas fixas ativas → transações `PENDING` com `source=FIXED`
  - Parcelas de cartão pendentes do mês → vincula ou cria transações com `source=CREDIT_CARD`
- Inicialização DEVE ser idempotente (re-execução segura sem duplicatas).
- Exclusão de período DEVE ser bloqueada se houver transações `PAID` vinculadas.
- Exclusão permitida DEVE desvincular transações `PENDING`/`OVERDUE` (define `period_uid=null`).

## Restrições de Edição

- Transações com `source=CREDIT_CARD` ou `source=TRANSFER` NÃO PODEM ter valor alterado isoladamente.
- Alteração DEVE ser feita na entidade pai (Charge/Installment ou Transfer).
- O Service DEVE rejeitar tentativas de edição direta com exceção de domínio.

## Enums (Fonte de Verdade)

| Entidade | Campo | Valores |
|----------|-------|---------|
| Account | `type` | `CHECKING`, `SAVINGS`, `CASH`, `OTHER` |
| Category | `direction` | `INFLOW`, `OUTFLOW` |
| Transaction | `status` | `PENDING`, `PAID`, `OVERDUE` |
| Transaction | `source` | `MANUAL`, `CREDIT_CARD`, `FIXED`, `TRANSFER` |
| CreditCard | `card_type` | `PHYSICAL`, `VIRTUAL` |
