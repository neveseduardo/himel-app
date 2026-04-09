# Plano de Implementação: Gestão de Transações por Período

## Visão Geral

Implementação incremental das 5 áreas de melhoria na gestão de transações por período: reordenação da sidebar, eager loading com dados legíveis, agrupamento por tipo com subtotais, remoção em lote (detach) e criação vinculada via ModalDialog. Cada tarefa constrói sobre a anterior, finalizando com a integração completa e testes.

## Tarefas

- [ ] 1. Backend — Serviço e contrato de desvinculação em lote
  - [~] 1.1 Adicionar método `detachAllTransactions(string $periodUid, string $userUid): int` ao `PeriodServiceInterface`
    - Arquivo: `app/Domain/Period/Contracts/PeriodServiceInterface.php`
    - _Requisitos: 4.3, 4.4_
  - [~] 1.2 Implementar `detachAllTransactions` no `PeriodService`
    - Usar `DB::transaction` para atomicidade
    - Executar `UPDATE financial_transactions SET period_uid = NULL WHERE period_uid = ?` filtrado por `user_uid`
    - Retornar contagem de transações desvinculadas
    - Arquivo: `app/Domain/Period/Services/PeriodService.php`
    - _Requisitos: 4.3, 4.4_
  - [~] 1.3 Adicionar eager loading de `account` e `category` em `getTransactionsForPeriod`
    - Adicionar `->with(['account', 'category'])` ao query existente
    - Arquivo: `app/Domain/Period/Services/PeriodService.php`
    - _Requisitos: 2.1_

- [ ] 2. Backend — Controller, rotas e validação
  - [~] 2.1 Adicionar `period_uid` ao `StoreTransactionRequest`
    - Adicionar regra: `'period_uid' => ['nullable', 'uuid', 'exists:financial_periods,uid']`
    - Adicionar mensagens pt-BR correspondentes
    - Arquivo: `app/Domain/Transaction/Requests/StoreTransactionRequest.php`
    - _Requisitos: 5.8_
  - [~] 2.2 Atualizar `show()` no `PeriodPageController` para incluir `accounts` e `categories` como props
    - Buscar contas e categorias do usuário autenticado
    - Passar como props Inertia adicionais
    - Arquivo: `app/Domain/Period/Controllers/PeriodPageController.php`
    - _Requisitos: 5.4, 2.1, 2.2, 2.3_
  - [ ] 2.3 Implementar método `storeTransaction` no `PeriodPageController`
    - Aceitar `StoreTransactionRequest` e `string $uid`
    - Injetar `period_uid` nos dados validados
    - Delegar ao `TransactionService::create()`
    - Redirecionar para `finance.periods.show` com flash success
    - Incluir `try-catch` com `Log::error()` e flash error
    - Arquivo: `app/Domain/Period/Controllers/PeriodPageController.php`
    - _Requisitos: 5.5, 5.6, 5.7_
  - [ ] 2.4 Implementar método `detachTransactions` no `PeriodPageController`
    - Chamar `PeriodService::detachAllTransactions()`
    - Redirecionar com flash success incluindo contagem
    - Incluir `try-catch` com `Log::error()` e flash error
    - Arquivo: `app/Domain/Period/Controllers/PeriodPageController.php`
    - _Requisitos: 4.3, 4.5, 4.6_
  - [ ] 2.5 Registrar novas rotas em `Period/Routes/web.php`
    - `POST periods/{uid}/transactions` → `storeTransaction` (name: `finance.periods.transactions.store`)
    - `DELETE periods/{uid}/transactions` → `detachTransactions` (name: `finance.periods.transactions.detach`)
    - Arquivo: `app/Domain/Period/Routes/web.php`
    - _Requisitos: 4.3, 5.5_

- [ ] 3. Checkpoint — Validar backend
  - Rodar `vendor/bin/pint --dirty --format agent` para formatação
  - Rodar `php artisan route:list --name=finance.periods` para verificar rotas
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 4. Backend — Testes de feature (PHPUnit)
  - [ ] 4.1 Criar teste `PeriodTransactionManagementTest` com cenários de detach
    - `test_user_can_detach_all_transactions_from_period` — criar período com transações, DELETE, verificar `period_uid = null` e nenhuma deletada
    - `test_detach_returns_success_message_with_count` — verificar flash success com contagem
    - `test_detach_empty_period_returns_success` — período sem transações retorna sucesso
    - `test_user_cannot_detach_transactions_from_another_users_period` — isolamento multi-tenant
    - Arquivo: `tests/Feature/PeriodTransactionManagementTest.php`
    - _Requisitos: 4.3, 4.4, 4.5, 4.6_
  - [ ] 4.2 Adicionar cenários de store transaction ao mesmo arquivo de teste
    - `test_user_can_create_transaction_linked_to_period` — POST com `period_uid`, verificar vínculo
    - `test_store_transaction_redirects_to_period_show` — verificar redirect
    - `test_store_transaction_validates_period_uid` — UUID inválido retorna 422
    - `test_period_show_includes_accounts_and_categories_props` — verificar props na resposta
    - `test_period_show_includes_account_and_category_in_transactions` — verificar eager loading
    - Arquivo: `tests/Feature/PeriodTransactionManagementTest.php`
    - _Requisitos: 5.5, 5.6, 5.7, 5.8, 5.4, 2.1, 2.2, 2.3_
  - [ ] 4.3 Escrever teste de propriedade: Detach preserva existência das transações
    - **Property 1: Detach preserves transaction existence**
    - Gerar N transações (0 a 50) vinculadas a um período via factory, executar detach, verificar que todas existem com `period_uid = null` e contagem total inalterada
    - Mínimo 20 iterações com N variado
    - **Valida: Requisito 4.3**
  - [ ] 4.4 Escrever teste de propriedade: Criação com period_uid vincula corretamente
    - **Property 2: Transaction creation with period_uid links correctly**
    - Gerar dados válidos de transação com `period_uid`, criar, verificar vínculo e incremento de contagem
    - **Valida: Requisito 5.5**
  - [ ] 4.5 Escrever teste de propriedade: Validação de period_uid aceita UUIDs válidos e rejeita strings inválidas
    - **Property 3: period_uid validation accepts valid UUIDs and rejects invalid strings**
    - Gerar UUIDs válidos (existentes) e strings inválidas, testar validação do `StoreTransactionRequest`
    - **Valida: Requisito 5.8**

- [ ] 5. Checkpoint — Validar testes backend
  - Rodar `php artisan test --compact tests/Feature/PeriodTransactionManagementTest.php`
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 6. Frontend — Schema, form e sidebar
  - [ ] 6.1 Adicionar `period_uid` ao Zod schema de transação
    - Adicionar: `period_uid: z.string().uuid().nullable().optional()`
    - Arquivo: `resources/js/modules/finance/validations/transaction-schema.ts`
    - _Requisitos: 5.8_
  - [ ] 6.2 Adicionar props `periodUid?` e `periodDate?` ao `TransactionForm.vue`
    - Quando `periodUid` presente: incluir `period_uid` nos `initialValues`, usar `periodDate` como `occurred_at` default
    - Ajustar `action` URL para apontar para rota `finance.periods.transactions.store` quando `periodUid` presente
    - Arquivo: `resources/js/modules/finance/components/TransactionForm.vue`
    - _Requisitos: 5.2, 5.3_
  - [ ] 6.3 Reordenar item "Períodos" na sidebar para 2ª posição
    - Mover `{ title: 'Períodos', ... }` para índice 1 no array `financeNavItems`
    - Arquivo: `resources/js/components/AppSidebar.vue`
    - _Requisitos: 1.1, 1.2, 1.3_

- [ ] 7. Frontend — Página Show.vue com agrupamento, criação e remoção
  - [ ] 7.1 Adicionar props `accounts` e `categories` ao `Show.vue`
    - Atualizar `defineProps` com `accounts: Account[]` e `categories: Category[]`
    - _Requisitos: 5.4_
  - [ ] 7.2 Implementar agrupamento por INFLOW/OUTFLOW com subtotais
    - Criar `computed` properties para filtrar transações por `direction`
    - Calcular subtotais por seção (soma dos `amount` por grupo)
    - Renderizar duas seções visuais: "Entradas" e "Saídas" com tabelas separadas
    - Exibir mensagem informativa quando seção estiver vazia
    - Arquivo: `resources/js/pages/finance/periods/Show.vue`
    - _Requisitos: 3.1, 3.2, 3.3_
  - [ ] 7.3 Adicionar botão "Nova Transação" com ModalDialog e TransactionForm
    - Botão no header que abre `ModalDialog`
    - Integrar `TransactionForm` passando `periodUid` e `periodDate` como props
    - Calcular `periodDate` como primeiro dia do mês/ano do período
    - Fechar modal e exibir toast no sucesso
    - Arquivo: `resources/js/pages/finance/periods/Show.vue`
    - _Requisitos: 5.1, 5.2, 5.3, 5.7_
  - [ ] 7.4 Adicionar botão "Remover Todas as Transações" com AlertDialog de confirmação
    - Botão no header com variante destrutiva
    - Usar `AlertDialog` do Shadcn/Vue para confirmação
    - Chamar `router.delete` para rota `finance.periods.transactions.detach`
    - Exibir toast de sucesso/erro
    - Arquivo: `resources/js/pages/finance/periods/Show.vue`
    - _Requisitos: 4.1, 4.2, 4.6_

- [ ] 8. Frontend — Gerar rotas Wayfinder e validar build
  - Rodar `php artisan wayfinder:generate` para gerar rotas tipadas das novas rotas
  - Rodar `npm run lint` para verificar ESLint
  - Rodar `npx vue-tsc --noEmit` para verificar tipos
  - Rodar `npm run build` para validar build
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 9. Checkpoint final — Validação completa
  - Rodar `vendor/bin/pint --dirty --format agent`
  - Rodar `php artisan test --compact tests/Feature/PeriodTransactionManagementTest.php`
  - Ensure all tests pass, ask the user if questions arise.

## Notas

- Tarefas marcadas com `*` são opcionais e podem ser puladas para um MVP mais rápido
- Cada tarefa referencia requisitos específicos para rastreabilidade
- Checkpoints garantem validação incremental
- Testes de propriedade validam invariantes de corretude universais
- Testes unitários validam exemplos específicos e edge cases
