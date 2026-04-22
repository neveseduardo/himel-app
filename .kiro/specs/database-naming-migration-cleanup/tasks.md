# Plano de Implementação: Limpeza de Nomenclatura do Banco de Dados

## Visão Geral

Refatoração completa para remover o prefixo redundante `financial_` de tabelas, colunas, factories, seeders, rotas (`finance.`), URLs (`/finance`) e caminhos de páginas Inertia (`finance/`). A execução segue a ordem de dependência definida no design: dados → suporte → lógica → rotas → controllers → frontend → testes → verificação.

## Tarefas

- [x] 1. Consolidar e renomear migrations
  - [x] 1.1 Editar as 9 migrations de criação para remover o prefixo `financial_` dos nomes de tabelas e foreign keys
    - Trocar `Schema::create('financial_X', ...)` por `Schema::create('X', ...)` em cada migration
    - Atualizar todas as referências de foreign key de `financial_X` para `X`
    - Na migration `create_credit_card_installments_table`: renomear coluna `financial_transaction_uid` → `transaction_uid`
    - _Requisitos: 1.1, 1.2, 1.3, 2.4_

  - [x] 1.2 Consolidar as 5 migrations incrementais nas migrations de criação correspondentes
    - Incorporar `period_uid` (nullable, FK → periods) e `description` (string 255, nullable) na migration `create_transactions_table`
    - Tornar `category_uid` nullable na migration `create_transactions_table`
    - Incorporar `closing_day` (tinyInt, nullable) e `last_four_digits` (string 4, nullable) na migration `create_credit_cards_table`
    - Incorporar `purchase_date` (date, nullable) na migration `create_credit_card_charges_table`
    - _Requisitos: 2.1, 2.3_

  - [x] 1.3 Remover os 5 arquivos de migrations incrementais
    - `2026_04_09_194739_add_period_uid_to_financial_transactions.php`
    - `2026_04_10_102543_add_description_to_financial_transactions_table.php`
    - `2026_04_20_115934_add_closing_day_and_last_four_digits_to_financial_credit_cards_table.php`
    - `2026_04_21_171509_add_purchase_date_to_financial_credit_card_charges_table.php`
    - `2026_04_22_002939_make_category_uid_nullable_in_financial_transactions.php`
    - _Requisitos: 2.2_

- [x] 2. Atualizar Models Eloquent
  - [x] 2.1 Atualizar a propriedade `$table` nos 9 Models para usar nomes limpos (sem `financial_`)
    - Account → `accounts`, Category → `categories`, Period → `periods`, CreditCard → `credit_cards`, Transfer → `transfers`, FixedExpense → `fixed_expenses`, CreditCardCharge → `credit_card_charges`, Transaction → `transactions`, CreditCardInstallment → `credit_card_installments`
    - _Requisitos: 3.1, 3.3_

  - [x] 2.2 Atualizar o Model `CreditCardInstallment`: `$fillable` e relationship `transaction()`
    - `financial_transaction_uid` → `transaction_uid` no array `$fillable`
    - Atualizar foreign key no método `transaction()` de `financial_transaction_uid` para `transaction_uid`
    - _Requisitos: 3.2_

- [x] 3. Renomear Factories
  - [x] 3.1 Renomear os 9 arquivos de factory e suas classes internas (remover prefixo `Financial`)
    - `FinancialAccountFactory` → `AccountFactory`, `FinancialCategoryFactory` → `CategoryFactory`, etc.
    - Atualizar o nome da classe dentro de cada arquivo
    - No `CreditCardInstallmentFactory`: atualizar referência `financial_transaction_uid` → `transaction_uid`
    - _Requisitos: 4.1, 4.2_

  - [x] 3.2 Atualizar referências às factories nos 9 Models (PHPDoc `@use HasFactory<...>` e imports)
    - _Requisitos: 4.3_

- [x] 4. Renomear Seeders
  - [x] 4.1 Renomear os 10 arquivos de seeder e suas classes internas (remover prefixo `Financial`)
    - 9 seeders de domínio: `FinancialAccountSeeder` → `AccountSeeder`, etc.
    - Seeder orquestrador: `FinancialSeeder` → `FinanceSeeder`
    - _Requisitos: 5.1, 5.2_

  - [x] 4.2 Atualizar `DatabaseSeeder` e `FinanceSeeder` para referenciar os novos nomes
    - `DatabaseSeeder`: chamar `FinanceSeeder` em vez de `FinancialSeeder`
    - `FinanceSeeder`: atualizar chamadas para os novos nomes de seeders
    - _Requisitos: 5.3, 5.4_

  - [x] 4.3 Atualizar `E2eTestSeeder` para usar os novos nomes de factories e seeders
    - Atualizar imports e chamadas de factory (ex: `FinancialAccountFactory::new()` → `AccountFactory::new()`)
    - _Requisitos: 4.4, 9.1, 9.2_

- [x] 5. Checkpoint — Executar `migrate:fresh --seed` e verificar integridade do banco
  - Executar `php artisan migrate:fresh --seed` — deve completar sem erros
  - Executar `vendor/bin/pint --dirty --format agent` — deve reportar 0 erros
  - Perguntar ao usuário se há dúvidas antes de prosseguir

- [x] 6. Atualizar Services e Form Requests
  - [x] 6.1 Atualizar `CreditCardChargeService` e `PeriodService` para usar `transaction_uid`
    - `CreditCardChargeService`: `'financial_transaction_uid' => ...` → `'transaction_uid' => ...`
    - `PeriodService`: `$installment->financial_transaction_uid` → `$installment->transaction_uid`
    - _Requisitos: 7.1, 7.2, 7.3_

  - [x] 6.2 Atualizar Form Requests com regras `exists:` e `unique:` referenciando tabelas prefixadas
    - `StoreTransactionRequest`: `exists:financial_periods,uid` → `exists:periods,uid`
    - Verificar todos os outros Form Requests para referências `financial_`
    - _Requisitos: 6.1, 6.2_

- [x] 7. Atualizar Rotas — remover prefixo `finance.` e `/finance`
  - [x] 7.1 Editar `routes/web.php`: remover grupo `Route::prefix('finance')->name('finance.')` e mover requires para o grupo `auth + verified`
    - Remover ou realocar a rota `Inertia::render('finance/Index')`
    - _Requisitos: 11.1_

  - [x] 7.2 Atualizar os 5 arquivos de rotas de domínio para remover prefixo `finance.` dos nomes
    - `CreditCard/Routes/web.php`: `->names('credit-cards')`
    - `Category/Routes/web.php`: `->names('categories')`
    - `Period/Routes/web.php`: `->names('periods')` + rotas nomeadas `periods.*`
    - `FixedExpense/Routes/web.php`: `->names('fixed-expenses')`
    - `CreditCardCharge/Routes/web.php`: `->names('credit-card-charges')`
    - _Requisitos: 11.2, 11.3, 15.1_

- [x] 8. Atualizar Controllers — `Inertia::render()` e `redirect()->route()`
  - [x] 8.1 Atualizar todos os PageControllers para remover prefixo `finance/` do `Inertia::render()`
    - `AccountPageController`, `CategoryPageController`, `CreditCardPageController`, `CreditCardChargePageController`, `FixedExpensePageController`, `PeriodPageController`, `TransactionPageController`, `TransferPageController`
    - Ex: `Inertia::render('finance/accounts/Index')` → `Inertia::render('accounts/Index')`
    - _Requisitos: 13.1, 13.3_

  - [x] 8.2 Atualizar todos os `redirect()->route('finance.*')` nos Controllers para remover prefixo `finance.`
    - Ex: `redirect()->route('finance.accounts.index')` → `redirect()->route('accounts.index')`
    - Corrigir bug existente no `PeriodPageController`: `finance.finance.periods.*` → `periods.*`
    - _Requisitos: 11.4, 13.2_

- [x] 9. Reestruturar Frontend — mover páginas, atualizar sidebar, regenerar Wayfinder
  - [x] 9.1 Mover páginas Inertia de `resources/js/pages/finance/<domínio>/` para `resources/js/pages/<domínio>/`
    - Mover 9 subdiretórios: accounts, categories, credit-cards, credit-card-charges, fixed-expenses, periods, transactions, transfers
    - Decidir destino de `finance/Index.vue` (remover ou incorporar)
    - Remover diretório `resources/js/pages/finance/` vazio
    - _Requisitos: 12.1, 12.2, 12.3, 12.4_

  - [x] 9.2 Atualizar `AppSidebar.vue` — remover prefixo `/finance` de todos os `href`
    - Atualizar array `financeNavItems` com URLs limpas
    - Atualizar lógica `isActive` que verifica `href === '/finance'`
    - _Requisitos: 16.1, 16.2, 16.3_

  - [x] 9.3 Atualizar referências a nomes de rotas `finance.*` no frontend (stores, composables, adapters, componentes Vue)
    - _Requisitos: 15.2_

  - [x] 9.4 Executar `php artisan wayfinder:generate` para regenerar rotas e actions tipadas
    - Verificar que `resources/js/routes/` não contém referências a `/finance`
    - Verificar que `resources/js/actions/` está atualizado
    - _Requisitos: 14.1, 14.2, 14.3_

- [x] 10. Checkpoint — Verificar frontend e rotas
  - Executar `npm run types:check` — deve reportar 0 erros TypeScript
  - Executar `php artisan route:list --name=finance` — deve retornar 0 resultados
  - Executar `vendor/bin/pint --dirty --format agent` — deve reportar 0 erros
  - Perguntar ao usuário se há dúvidas antes de prosseguir
  - _Requisitos: 14.4_

- [x] 11. Atualizar testes PHPUnit
  - [x] 11.1 Atualizar referências a tabelas e colunas nos 9 arquivos de teste
    - `assertDatabaseHas('financial_X', ...)` → `assertDatabaseHas('X', ...)`
    - `assertDatabaseMissing('financial_X', ...)` → `assertDatabaseMissing('X', ...)`
    - `assertDatabaseCount('financial_X', ...)` → `assertDatabaseCount('X', ...)`
    - `DB::table('financial_X')` → `DB::table('X')`
    - `'financial_transaction_uid'` → `'transaction_uid'`
    - Arquivos: `PeriodInitializationTest`, `PeriodCreationAndDeletionTest`, `PeriodViewAndFilterTest`, `PeriodTransactionManagementTest`, `PeriodServiceInitializeTest`, `PeriodServiceDeleteTest`, `PeriodServiceCreateTest`, `CreateDefaultCategoriesListenerTest`, `MarkOverdueTransactionsCommandTest`
    - _Requisitos: 8.1, 8.2, 8.3_

- [x] 12. Atualizar testes E2E — Page Objects e URLs
  - [x] 12.1 Atualizar o método `goto()` nos 6 Page Objects para remover prefixo `/finance` da URL
    - `AccountPage.ts`, `CreditCardPage.ts`, `CreditCardChargePage.ts`, `TransferPage.ts`, `FixedExpensePage.ts`, `TransactionPage.ts`
    - _Requisitos: 17.1, 17.2_

  - [x] 12.2 Atualizar referências a nomes de rotas `finance.*` nos testes E2E (se houver)
    - _Requisitos: 15.3_

- [x] 13. Verificação final de integridade
  - Executar `php artisan migrate:fresh --seed` — deve completar sem erros
  - Executar `php artisan test --compact` — deve ter 0 falhas
  - Executar `vendor/bin/pint --dirty --format agent` — deve reportar 0 erros
  - Executar `npm run types:check` — deve reportar 0 erros TypeScript
  - Executar grep por referências residuais `financial_` em PHP (exceto `personal_access_tokens`)
  - Executar grep por referências residuais `finance.` em PHP/Vue/TS
  - Executar grep por referências residuais `/finance` em Vue/TS/E2E
  - Executar `php artisan route:list --name=finance` — deve retornar 0 resultados
  - _Requisitos: 10.1, 10.2, 10.3, 10.4, 10.5_

## Notas

- O projeto NÃO está em produção — usar `migrate:fresh` em vez de migrations de renomeação
- Property-Based Testing não se aplica — esta é uma refatoração estrutural sem lógica algorítmica
- Cada tarefa referencia requisitos específicos para rastreabilidade
- Checkpoints garantem validação incremental entre camadas
- A ordem de execução é crítica: dados → suporte → lógica → rotas → controllers → frontend → testes
