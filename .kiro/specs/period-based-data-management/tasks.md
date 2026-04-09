# Plano de Implementação — Gestão de Dados por Período

## Fase 1: Backend — Schema e Models

- [x] 1. Criar migration e atualizar models
  - [x] 1.1 Criar migration `add_period_uid_to_financial_transactions` adicionando coluna `period_uid` (UUID, nullable) com FK para `financial_periods.uid` (onDelete set null) e índice
  - [x] 1.2 Adicionar `period_uid` ao `$fillable` do model `Transaction` e criar relacionamento `period()` belongsTo com `Period`
  - [x] 1.3 Adicionar relacionamento `transactions()` hasMany no model `Period` via `period_uid`
  - [x] 1.4 Executar migration e verificar schema com `php artisan migrate`

## Fase 2: Backend — Service Layer

- [x] 2. Criar exceções de domínio
  - [x] 2.1 Criar `PeriodAlreadyExistsException` em `app/Domain/Period/Exceptions/`
  - [x] 2.2 Criar `PeriodHasPaidTransactionsException` em `app/Domain/Period/Exceptions/`

- [x] 3. Atualizar PeriodServiceInterface com novos métodos
  - [x] 3.1 Adicionar método `create(string $userUid, int $month, int $year): Period` à interface
  - [x] 3.2 Adicionar método `initializePeriod(string $uid, string $userUid): array` à interface
  - [x] 3.3 Adicionar método `getByUidWithSummary(string $uid, string $userUid): ?array` à interface
  - [x] 3.4 Adicionar método `getTransactionsForPeriod(string $periodUid, string $userUid, array $filters = []): array` à interface

- [x] 4. Implementar lógica de criação de período com validação de duplicata
  - [x] 4.1 Implementar `PeriodService::create()` que verifica existência de período com mesmo mês/ano/usuário e lança `PeriodAlreadyExistsException` se já existir

- [x] 5. Implementar lógica de inicialização de período
  - [x] 5.1 Implementar geração de transações a partir de despesas fixas ativas (status PENDING, source FIXED, direction OUTFLOW, com clamping de due_date)
  - [x] 5.2 Implementar identificação e vinculação/criação de transações a partir de parcelas de cartão de crédito elegíveis
  - [x] 5.3 Implementar verificação de idempotência (ignorar itens já existentes no período via reference_id + period_uid)
  - [x] 5.4 Garantir execução dentro de `DB::transaction` e retorno do resumo (fixed_created, installments_linked, installments_created, skipped)

- [x] 6. Implementar lógica de exclusão de período com validação
  - [x] 6.1 Atualizar `PeriodService::delete()` para verificar transações PAID e lançar `PeriodHasPaidTransactionsException`
  - [x] 6.2 Implementar desvinculação de transações PENDING/OVERDUE (period_uid = null) antes da exclusão, dentro de `DB::transaction`

- [x] 7. Implementar consultas de período com resumo e transações
  - [x] 7.1 Implementar `getByUidWithSummary()` retornando período com totais de INFLOW, OUTFLOW e saldo
  - [x] 7.2 Implementar `getTransactionsForPeriod()` com filtros por status, direction, source e paginação

## Fase 3: Backend — Controllers e Rotas

- [x] 8. Atualizar PeriodPageController (Inertia)
  - [x] 8.1 Atualizar método `index()` para incluir contagem de transações por período (`withCount('transactions')`)
  - [x] 8.2 Adicionar método `store()` para criação de período com tratamento de `PeriodAlreadyExistsException`
  - [x] 8.3 Adicionar método `show()` renderizando `finance/periods/Show` com resumo financeiro e transações paginadas
  - [x] 8.4 Adicionar método `destroy()` com tratamento de `PeriodHasPaidTransactionsException`
  - [x] 8.5 Adicionar método `initialize()` que aciona `initializePeriod()` e redireciona com flash de resumo

- [x] 9. Atualizar PeriodController (API)
  - [x] 9.1 Atualizar método `store()` para usar `PeriodService::create()` e retornar 409 em caso de duplicata
  - [x] 9.2 Adicionar método `initialize()` que aciona `initializePeriod()` e retorna resumo com HTTP 200
  - [x] 9.3 Atualizar método `destroy()` para tratar `PeriodHasPaidTransactionsException` com HTTP 422

- [x] 10. Atualizar rotas
  - [x] 10.1 Atualizar `app/Domain/Period/Routes/web.php` adicionando store, show, destroy ao resource e rota POST `periods/{uid}/initialize`
  - [x] 10.2 Atualizar `app/Domain/Period/Routes/api.php` adicionando rota POST `periods/{uid}/initialize`

- [x] 11. Atualizar PeriodResource
  - [x] 11.1 Adicionar `transactions_count` via `whenCounted('transactions')` ao `PeriodResource`

## Fase 4: Frontend

- [x] 12. Atualizar tipos TypeScript
  - [x] 12.1 Adicionar `transactions_count` ao tipo `Period`, criar tipos `PeriodSummary` e `InitializationResult` em `resources/js/modules/finance/types/finance.ts`

- [x] 13. Atualizar página Index de períodos
  - [x] 13.1 Adicionar coluna de contagem de transações na tabela
  - [x] 13.2 Adicionar botão "Criar Período" com modal/dialog para seleção de mês e ano
  - [x] 13.3 Adicionar links de navegação para a página Show de cada período
  - [x] 13.4 Adicionar botão de exclusão com dialog de confirmação

- [x] 14. Criar página Show de períodos
  - [x] 14.1 Criar `resources/js/pages/finance/periods/Show.vue` com cabeçalho (mês/ano), resumo financeiro (entradas, saídas, saldo) e botão "Inicializar Período"
  - [x] 14.2 Implementar tabela de transações com colunas: descrição, categoria, conta, valor, vencimento, status, ações
  - [x] 14.3 Implementar filtros de transações por status, direção e fonte
  - [x] 14.4 Implementar ação de marcar transação como paga na lista
  - [x] 14.5 Implementar exibição de notificação com resumo após inicialização

- [x] 15. Gerar rotas Wayfinder
  - [x] 15.1 Executar `php artisan wayfinder:generate` após criação dos novos endpoints

## Fase 5: Testes

- [x] 16. Testes de feature — Criação e exclusão de período
  - [x] 16.1 Testar criação de período com dados válidos (HTTP 201)
  - [x] 16.2 Testar rejeição de período duplicado (HTTP 409)
  - [x] 16.3 Testar validação de campos month/year inválidos (HTTP 422)
  - [x] 16.4 Testar exclusão de período sem transações
  - [x] 16.5 Testar rejeição de exclusão com transações PAID
  - [x] 16.6 Testar exclusão com desvinculação de transações PENDING/OVERDUE

- [x] 17. Testes de feature — Inicialização de período
  - [x] 17.1 Testar inicialização com despesas fixas ativas (criação de transações corretas)
  - [x] 17.2 Testar inicialização com parcelas de cartão (vinculação e criação)
  - [x] 17.3 Testar idempotência (re-execução sem duplicatas)
  - [x] 17.4 Testar resumo da inicialização (contagens corretas)
  - [x] 17.5 Testar clamping de due_date para meses curtos (fev, abr, jun, set, nov)
  - [x] 17.6 Testar inicialização incremental (nova despesa fixa adicionada entre execuções)

- [x] 18. Testes de feature — Visualização e filtragem
  - [x] 18.1 Testar página show com resumo financeiro correto
  - [x] 18.2 Testar filtragem de transações por período, status, direção e fonte
  - [x] 18.3 Testar relacionamentos Transaction→Period e Period→Transactions
