# 📊 SaaS Financeiro — Regras de Negócio e Fluxo Funcional

Este documento serve como a **Especificação de Negócio** obrigatória para o Agente de IA. Nenhuma funcionalidade deve ser implementada se divergir das definições abaixo.

---

## 1. VISÃO DE PRODUTO & PRINCÍPIOS

O sistema visa controle financeiro simples, automático e previsível.

- **Foco:** Clareza de saldo, previsibilidade (projeções) e simplicidade.
- **Conceito Central:** O sistema baseia-se em movimentações vinculadas a contas. Não há fechamento de mês manual; o sistema é um fluxo contínuo.

---

## 2. REGRAS DE INTEGRIDADE (GUARDRAILS)

- **Multi-tenancy:** Todo registro (`Account`, `Category`, `Transaction`, etc.) deve obrigatoriamente possuir um `user_uid`. O Agente deve garantir que o escopo de qualquer consulta seja restrito ao usuário autenticado.
- **Valores Monetários:** O campo `amount` deve ser sempre um decimal positivo. A natureza da transação (entrada/saída) é definida pelo campo `direction`.
- **Identificadores:** Uso exclusivo de **UUID v4** para todas as chaves primárias e estrangeiras.

---

## 3. FLUXO DE USUÁRIO & ONBOARDING

- **Restrição de Entrada:** O usuário **não pode** registrar transações ou despesas fixas sem antes possuir ao menos uma `FinancialAccount` criada.
- **Categorias Padrão:** No primeiro acesso, o sistema deve sugerir ou criar automaticamente um conjunto de categorias básicas (Ex: Alimentação, Aluguel, Salário).

---

## 4. DOMÍNIO: TRANSAÇÕES (FINANCIAL TRANSACTIONS)

### 4.1 Regras de Criação

- **Campos Obrigatórios:** `amount`, `financial_account_uid`, `financial_category_uid`, `occurred_at`, `direction`.
- **Validação de Categoria:** O sistema deve impedir vincular uma categoria de `direction: INFLOW` a uma transação de `direction: OUTFLOW`.

### 4.2 Estados e Ciclo de Vida

- **Status PENDING:** Transações com `due_date` futuro.
- **Status PAID:** Quando o usuário confirma o pagamento. Deve preencher `paid_at`.
- **Status OVERDUE:** Automatizado. Toda transação `PENDING` onde `due_date` < `now()` deve ser tratada como atrasada.
- **Cálculo de Saldo:** O saldo da `FinancialAccount` deve ser atualizado (incrementado ou decrementado) apenas por transações com status `PAID`.

---

## 5. DOMÍNIO: CARTÃO DE CRÉDITO

### 5.1 Estrutura de Compra

- **Charge (Compra):** Registro pai que contém o valor total e o número de parcelas.
- **Installments (Parcelas):** O sistema deve gerar automaticamente `N` parcelas na tabela `financial_credit_card_installments`.
- **Projeção:** Cada parcela deve gerar uma `FinancialTransaction` vinculada (via `reference_id`), com o `due_date` calculado com base no `due_day` do cartão e no mês de cada parcela.

---

## 6. DOMÍNIO: DESPESAS FIXAS E TRANSFERÊNCIAS

### 6.1 Despesas Fixas

- **Comportamento:** O sistema deve projetar a transação para o mês atual/seguinte baseando-se no `due_day`.
- **Status:** Podem ser pausadas (campo `active: false`).

### 6.2 Transferências

- **Atomicidade:** Uma transferência é uma operação única que gera um débito na conta de origem e um crédito na conta de destino. Deve ser tratada via `DB::transaction`.

---

## 7. DOMÍNIO: PERÍODOS

- **Automatização:** O usuário não cria meses ou períodos. O sistema utiliza a tabela `financial_periods` para agrupar e indexar visualizações de dashboard de forma automática (Mês/Ano).

---

## 8. MATRIZ DE ENUMS (REFERÊNCIA TÉCNICA)

| Entidade        | Campo       | Valores Permitidos                           |
| :-------------- | :---------- | :------------------------------------------- |
| **Account**     | `type`      | `CHECKING`, `SAVINGS`, `CASH`, `OTHER`       |
| **Category**    | `direction` | `INFLOW`, `OUTFLOW`                          |
| **Transaction** | `status`    | `PENDING`, `PAID`, `OVERDUE`                 |
| **Transaction** | `source`    | `MANUAL`, `CREDIT_CARD`, `FIXED`, `TRANSFER` |
| **Credit Card** | `card_type` | `PHYSICAL`, `VIRTUAL`                        |

---

## 9. RESTRIÇÕES DE EDIÇÃO

- **Rastreabilidade:** Transações geradas por "Cartão de Crédito" ou "Transferências" não podem ter seu valor alterado isoladamente; a alteração deve ser feita na entidade pai (ex: na Parcela ou na Transferência) para manter a consistência.

---

## 10. ISOLAMENTO DE DADOS POR USUÁRIO

- Cada model deve pertencer a um usuário.
- Cada usuário deve ter acesso apenas aos seus próprios dados.
- O isolamento é feito através do campo `user_uid` presente em todos os registros.
- Relations entre models devem respeitar o isolamento por usuário.
