# Tarefas de Implementação — Despesas Fixas e Parcelas de Cartão na Visualização do Período

## Tarefa 1: Backend — Novos métodos no PeriodService e PeriodServiceInterface

- [x] 1.1 Adicionar assinaturas dos métodos `getFixedExpensesForPeriod`, `getInstallmentsForPeriod` e `getCardBreakdownForPeriod` na interface `PeriodServiceInterface`
- [x] 1.2 Implementar `getFixedExpensesForPeriod` no `PeriodService`: buscar transações FIXED do período, resolver reference_id → FixedExpense (com category), retornar items + subtotal, tratar referências inválidas com campos nulos
- [x] 1.3 Implementar `getInstallmentsForPeriod` no `PeriodService`: buscar transações CREDIT_CARD do período, resolver reference_id → CreditCardInstallment (com charge.creditCard), retornar items com installment_number/total_installments + subtotal, tratar referências inválidas
- [-] 1.4 Implementar `getCardBreakdownForPeriod` no `PeriodService`: agrupar parcelas por cartão de crédito, retornar cards[] com nome e total por cartão + grand_total
- [~] 1.5 Estender `getByUidWithSummary` no `PeriodService`: adicionar subtotais por source (total_fixed_expenses, total_credit_card_installments, total_manual, total_transfer) ao retorno existente

## Tarefa 2: Backend — Atualizar PeriodPageController::show

- [~] 2.1 Chamar os novos métodos do PeriodService no método `show` do `PeriodPageController` e passar os dados como props Inertia adicionais (fixed_expenses, installments, card_breakdown, summary expandido)

## Tarefa 3: Frontend — Tipos TypeScript

- [~] 3.1 Adicionar interfaces `PeriodFixedExpenseItem`, `PeriodFixedExpenses`, `PeriodInstallmentItem`, `PeriodInstallments`, `CardBreakdownItem`, `PeriodCardBreakdown` em `resources/js/domain/Period/types/period.ts` e estender `PeriodSummary` com campos opcionais de subtotais por fonte

## Tarefa 4: Frontend — Seção de Despesas Fixas na Show.vue

- [~] 4.1 Adicionar prop `fixedExpenses` (PeriodFixedExpenses) na página Show.vue
- [~] 4.2 Renderizar seção "Despesas Fixas" com Card, cabeçalho com subtotal, tabela com nome/valor/categoria/vencimento, e mensagem de estado vazio "Nenhuma despesa fixa neste período."

## Tarefa 5: Frontend — Seção de Parcelas de Cartão na Show.vue

- [~] 5.1 Adicionar prop `installments` (PeriodInstallments) na página Show.vue
- [~] 5.2 Renderizar seção "Parcelas de Cartão" com Card, cabeçalho com subtotal, tabela com descrição + badge "X/Y", valor, vencimento, nome do cartão, e mensagem de estado vazio "Nenhuma parcela de cartão neste período."

## Tarefa 6: Frontend — Seção de Resumo por Cartão na Show.vue

- [~] 6.1 Adicionar prop `cardBreakdown` (PeriodCardBreakdown) na página Show.vue
- [~] 6.2 Renderizar seção "Resumo por Cartão" com Card mostrando cada cartão e valor total, total geral no rodapé, ocultar seção quando não há parcelas

## Tarefa 7: Frontend — Resumo financeiro expandido

- [~] 7.1 Atualizar os cards de resumo financeiro existentes para exibir subtotais de composição de saídas (despesas fixas, parcelas de cartão, manuais, transferências) usando os novos campos do PeriodSummary

## Tarefa 8: Frontend — Tratamento de dados nulos

- [~] 8.1 Garantir que todos os campos potencialmente nulos (description, category_name, charge_description, installment_number, total_installments, credit_card_name) exibam "—" quando nulos, sem causar erro de renderização

## Tarefa 9: Testes unitários (PHPUnit)

- [~] 9.1 Criar teste para `getFixedExpensesForPeriod`: cenários com 0, 1 e N despesas fixas, e com reference_id inválido
- [~] 9.2 Criar teste para `getInstallmentsForPeriod`: cenários com 0, 1 e N parcelas, reference_id inválido, verificar installment_number e total_installments
- [~] 9.3 Criar teste para `getCardBreakdownForPeriod`: agrupamento com 1 e N cartões, verificar totais
- [~] 9.4 Criar teste para `getByUidWithSummary` expandido: verificar subtotais por source e invariante soma = total_outflow
