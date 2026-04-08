# Regras de Negócio — SaaS Financeiro Himel App

> **Glob:** `app/Domain/**/*.php`
>
> Este arquivo define as regras de negócio obrigatórias do domínio financeiro. O agente DEVE consultar estas regras antes de implementar qualquer funcionalidade de domínio.

## Visão do Produto

O sistema é um SaaS de controle financeiro pessoal focado em clareza de saldo, previsibilidade (projeções) e simplicidade. O conceito central baseia-se em movimentações vinculadas a contas. NÃO existe fechamento de mês manual — o sistema opera como fluxo contínuo.

## Regras de Integridade

- **Multi-tenancy:** Todo registro (`Account`, `Category`, `Transaction`, etc.) DEVE possuir `user_uid`. SEMPRE restringir consultas ao usuário autenticado.
- **Valores monetários:** O campo `amount` DEVE ser sempre um decimal positivo. A natureza (entrada/saída) é definida pelo campo `direction`.
- **Identificadores:** É OBRIGATÓRIO o uso de UUID v4 para todas as chaves primárias e estrangeiras. É PROIBIDO usar IDs incrementais.

## Onboarding

- O usuário NÃO PODE registrar transações ou despesas fixas sem possuir ao menos uma `FinancialAccount`.
- No primeiro acesso, o sistema DEVE criar automaticamente categorias padrão:
  - **OUTFLOW:** Alimentação, Moradia, Transporte, Saúde, Educação, Lazer, Vestuário, Outros
  - **INFLOW:** Salário, Freelance, Investimentos, Outros

## Transações

### Criação

- Campos obrigatórios: `amount`, `financial_account_uid`, `financial_category_uid`, `occurred_at`, `direction`.
- O sistema DEVE impedir vincular uma categoria de `direction: INFLOW` a uma transação de `direction: OUTFLOW` (e vice-versa).

### Ciclo de Vida (Status)

| Status | Condição |
|--------|----------|
| `PENDING` | Transação com `due_date` futuro |
| `PAID` | Usuário confirma pagamento → preencher `paid_at` |
| `OVERDUE` | Automatizado: `PENDING` onde `due_date < now()` |

### Cálculo de Saldo

- O saldo da `FinancialAccount` DEVE ser atualizado (incrementado ou decrementado) APENAS por transações com status `PAID`.
- Transações `PENDING` ou `OVERDUE` NÃO afetam o saldo.

## Cartão de Crédito

- **Charge (Compra):** Registro pai com valor total e número de parcelas.
- **Installments (Parcelas):** O sistema DEVE gerar automaticamente `N` parcelas em `financial_credit_card_installments`.
- **Projeção:** Cada parcela DEVE gerar uma `FinancialTransaction` vinculada via `reference_id`, com `due_date` calculado a partir do `due_day` do cartão e mês de cada parcela.
- O número de parcelas DEVE estar entre 1 e 48.
- Centavos residuais da divisão DEVEM ser distribuídos na última parcela.

## Despesas Fixas e Transferências

### Despesas Fixas

- O sistema DEVE projetar a transação para o mês atual/seguinte baseando-se no `due_day`.
- Podem ser pausadas via campo `active: false`.

### Transferências

- Uma transferência é uma operação atômica: débito na conta de origem + crédito na conta de destino.
- DEVE ser tratada via `DB::transaction`.

## Períodos

- O usuário NÃO cria meses ou períodos manualmente.
- A tabela `financial_periods` é usada para agrupar e indexar visualizações de dashboard automaticamente (Mês/Ano).

## Enums

| Entidade | Campo | Valores Permitidos |
|----------|-------|--------------------|
| Account | `type` | `CHECKING`, `SAVINGS`, `CASH`, `OTHER` |
| Category | `direction` | `INFLOW`, `OUTFLOW` |
| Transaction | `status` | `PENDING`, `PAID`, `OVERDUE` |
| Transaction | `source` | `MANUAL`, `CREDIT_CARD`, `FIXED`, `TRANSFER` |
| CreditCard | `card_type` | `PHYSICAL`, `VIRTUAL` |

## Restrições de Edição

- Transações geradas por "Cartão de Crédito" ou "Transferências" NÃO PODEM ter seu valor alterado isoladamente.
- A alteração DEVE ser feita na entidade pai (Parcela ou Transferência) para manter consistência e rastreabilidade.

## Isolamento de Dados

- Cada model DEVE pertencer a um usuário via `user_uid`.
- Cada usuário DEVE ter acesso APENAS aos seus próprios dados.
- Relations entre models DEVEM respeitar o isolamento por usuário.
- É PROIBIDO expor dados de um usuário para outro.
