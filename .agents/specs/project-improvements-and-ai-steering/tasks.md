# Plano de Implementação — Melhorias do Projeto, Controllers Inertia e Steering

## Fase 1: Melhorias de Backend

- [x] 1. Refatorar API Controllers — remover DB::transaction duplicado
  - [x] 1.1 Remover `DB::transaction` dos métodos store/update/destroy do `AccountController`, delegando diretamente ao Service
  - [x] 1.2 Remover `DB::transaction` dos métodos store/update/destroy do `CategoryController`
  - [x] 1.3 Remover `DB::transaction` dos métodos store/update/destroy do `TransactionController`
  - [x] 1.4 Remover `DB::transaction` dos métodos store/destroy do `TransferController`
  - [x] 1.5 Remover `DB::transaction` dos métodos store/update/destroy do `FixedExpenseController`
  - [x] 1.6 Remover `DB::transaction` dos métodos store/update/destroy do `CreditCardController`
  - [x] 1.7 Remover `DB::transaction` dos métodos store/update/destroy do `CreditCardChargeController`
  - [x] 1.8 Remover `DB::transaction` do método markAsPaid do `CreditCardInstallmentController`
  - [x] 1.9 Remover `DB::transaction` dos métodos store/destroy do `PeriodController`

- [x] 2. Melhorar filtro de busca e campo description em Transações
  - [x] 2.1 Atualizar `TransactionService::getAllWithFilters` para buscar em `description` além de `account.name`
  - [x] 2.2 Adicionar `description` ao `$fillable` do model `Transaction` e ao array de criação no `TransactionService::create()`
  - [x] 2.3 Adicionar regra `'description' => 'nullable|string|max:255'` no `StoreTransactionRequest` e `UpdateTransactionRequest`

- [x] 3. Criar API Resources para todos os domínios
  - [x] 3.1 Criar `AccountResource` em `app/Domain/Account/Resources/`
  - [x] 3.2 Criar `CategoryResource` em `app/Domain/Category/Resources/`
  - [x] 3.3 Criar `TransactionResource` em `app/Domain/Transaction/Resources/` (com account e category nested via `whenLoaded`)
  - [x] 3.4 Criar `TransferResource` em `app/Domain/Transfer/Resources/` (com from_account e to_account nested)
  - [x] 3.5 Criar `FixedExpenseResource` em `app/Domain/FixedExpense/Resources/` (com category nested)
  - [x] 3.6 Criar `CreditCardResource` em `app/Domain/CreditCard/Resources/`
  - [x] 3.7 Criar `CreditCardChargeResource` em `app/Domain/CreditCardCharge/Resources/` (com credit_card e installments nested)
  - [x] 3.8 Criar `CreditCardInstallmentResource` em `app/Domain/CreditCardInstallment/Resources/` (com transaction nested)
  - [x] 3.9 Criar `PeriodResource` em `app/Domain/Period/Resources/`
  - [x] 3.10 Atualizar todos os API Controllers para usar as Resources nas respostas JSON

- [x] 4. Implementar comando OVERDUE e categorias padrão
  - [x] 4.1 Criar `MarkOverdueTransactionsCommand` em `app/Domain/Transaction/Commands/` com signature `transactions:mark-overdue`
  - [x] 4.2 Registrar o comando no schedule diário em `routes/console.php`
  - [x] 4.3 Criar `CreateDefaultCategoriesListener` em `app/Domain/Category/Listeners/` que escuta o evento `Login`
  - [x] 4.4 Registrar o listener no `EventServiceProvider` ou via atributo

- [x] 5. Melhorar validação de parcelas do cartão de crédito
  - [x] 5.1 Adicionar validação de limites (1-48 parcelas) no `CreditCardChargeService::create()`
  - [x] 5.2 Implementar distribuição de centavos residuais na última parcela
  - [x] 5.3 Garantir geração de `FinancialTransaction` vinculada (via `reference_id`) para cada parcela com source `CREDIT_CARD` e status `PENDING`

## Fase 2: Steering Files

- [x] 6. Converter instruções para steering files
  - [x] 6.1 Criar `.agents/steering/business-rules.md` a partir de `instructions/REGRAS_NEGOCIO.md` — reformatar em linguagem diretiva e concisa para agentes de IA
  - [x] 6.2 Criar `.agents/steering/development-protocol.md` a partir de `instructions/REGRAS_INFRAESTRUTURA.md` — incluir regras de backend, frontend, checklist e mapeamento de models
  - [x] 6.3 Criar `.agents/steering/database-schema.md` a partir de `instructions/modelagem.sql` — documentar tabelas, colunas, tipos, constraints e relacionamentos

## Fase 3: Infraestrutura Frontend

- [x] 7. Criar estrutura base do módulo finance no frontend
  - [x] 7.1 Criar tipos TypeScript em `resources/js/modules/finance/types/finance.ts` (Account, Category, Transaction, Transfer, FixedExpense, CreditCard, CreditCardCharge, CreditCardInstallment, Period, PaginationMeta)
  - [x] 7.2 Criar composable `useFinanceFilters.ts` em `resources/js/modules/finance/composables/` para lógica de filtros compartilhada
  - [x] 7.3 Criar composable `usePagination.ts` em `resources/js/modules/finance/composables/` para lógica de paginação
  - [x] 7.4 Criar composable `useFlashMessages.ts` em `resources/js/modules/finance/composables/` para leitura de flash messages do Inertia
  - [x] 7.5 Criar `finance.services.ts` em `resources/js/modules/finance/services/` com helpers de formatação (moeda BRL, datas pt-BR)
  - [x] 7.6 Criar componentes reutilizáveis: `DataTable.vue`, `FilterBar.vue`, `StatusBadge.vue`, `DirectionBadge.vue` em `resources/js/modules/finance/components/`
  - [x] 7.7 Criar schemas Zod de validação em `resources/js/modules/finance/validations/` para cada domínio (account, category, transaction, transfer, fixed-expense, credit-card, credit-card-charge)

- [x] 8. Configurar rotas web e navegação
  - [x] 8.1 Adicionar grupo de rotas `/finance` em `routes/web.php` com middleware `auth, verified`, incluindo arquivos de rota web de cada domínio
  - [x] 8.2 Criar página `resources/js/pages/finance/Index.vue` (dashboard financeiro — rota `/finance`)
  - [x] 8.3 Atualizar sidebar (`AppSidebarLayout.vue`) com itens de navegação financeira (Contas, Categorias, Transações, Transferências, Despesas Fixas, Cartões, Compras Cartão)
  - [x] 8.4 Gerar rotas Wayfinder (`php artisan wayfinder:generate`) após criar os PageControllers

## Fase 4: Controllers Inertia + Páginas Vue (por domínio)

- [x] 9. Domínio Account — PageController + Páginas Vue
  - [x] 9.1 Criar `AccountPageController` em `app/Domain/Account/Controllers/` com métodos index, create, store, edit, update, destroy usando `Inertia::render`
  - [x] 9.2 Criar arquivo de rotas web `app/Domain/Account/Routes/web.php` com `Route::resource('accounts', AccountPageController::class)->parameters(['accounts' => 'uid'])->names('finance.accounts')`
  - [x] 9.3 Criar `AccountForm.vue` em `resources/js/modules/finance/components/` com validação Zod
  - [x] 9.4 Criar páginas `Index.vue`, `Create.vue`, `Edit.vue` em `resources/js/pages/finance/accounts/`

- [x] 10. Domínio Category — PageController + Páginas Vue
  - [x] 10.1 Criar `CategoryPageController` em `app/Domain/Category/Controllers/`
  - [x] 10.2 Criar arquivo de rotas web `app/Domain/Category/Routes/web.php`
  - [x] 10.3 Criar `CategoryForm.vue` em `resources/js/modules/finance/components/`
  - [x] 10.4 Criar páginas `Index.vue`, `Create.vue`, `Edit.vue` em `resources/js/pages/finance/categories/`

- [x] 11. Domínio Transaction — PageController + Páginas Vue
  - [x] 11.1 Criar `TransactionPageController` em `app/Domain/Transaction/Controllers/` (index deve passar accounts e categories como props para os selects)
  - [x] 11.2 Criar arquivo de rotas web `app/Domain/Transaction/Routes/web.php`
  - [x] 11.3 Criar `TransactionForm.vue` em `resources/js/modules/finance/components/`
  - [x] 11.4 Criar páginas `Index.vue`, `Create.vue`, `Edit.vue` em `resources/js/pages/finance/transactions/`

- [x] 12. Domínio Transfer — PageController + Páginas Vue
  - [x] 12.1 Criar `TransferPageController` em `app/Domain/Transfer/Controllers/` (index, create, store, destroy)
  - [x] 12.2 Criar arquivo de rotas web `app/Domain/Transfer/Routes/web.php`
  - [x] 12.3 Criar `TransferForm.vue` em `resources/js/modules/finance/components/`
  - [x] 12.4 Criar páginas `Index.vue`, `Create.vue` em `resources/js/pages/finance/transfers/`

- [x] 13. Domínio FixedExpense — PageController + Páginas Vue
  - [x] 13.1 Criar `FixedExpensePageController` em `app/Domain/FixedExpense/Controllers/`
  - [x] 13.2 Criar arquivo de rotas web `app/Domain/FixedExpense/Routes/web.php`
  - [x] 13.3 Criar `FixedExpenseForm.vue` em `resources/js/modules/finance/components/`
  - [x] 13.4 Criar páginas `Index.vue`, `Create.vue`, `Edit.vue` em `resources/js/pages/finance/fixed-expenses/`

- [x] 14. Domínio CreditCard — PageController + Páginas Vue
  - [x] 14.1 Criar `CreditCardPageController` em `app/Domain/CreditCard/Controllers/`
  - [x] 14.2 Criar arquivo de rotas web `app/Domain/CreditCard/Routes/web.php`
  - [x] 14.3 Criar `CreditCardForm.vue` em `resources/js/modules/finance/components/`
  - [x] 14.4 Criar páginas `Index.vue`, `Create.vue`, `Edit.vue` em `resources/js/pages/finance/credit-cards/`

- [x] 15. Domínio CreditCardCharge — PageController + Páginas Vue
  - [x] 15.1 Criar `CreditCardChargePageController` em `app/Domain/CreditCardCharge/Controllers/` (index, create, store, show)
  - [x] 15.2 Criar arquivo de rotas web `app/Domain/CreditCardCharge/Routes/web.php`
  - [x] 15.3 Criar `CreditCardChargeForm.vue` em `resources/js/modules/finance/components/`
  - [x] 15.4 Criar páginas `Index.vue`, `Create.vue`, `Show.vue` em `resources/js/pages/finance/credit-card-charges/`

- [x] 16. Domínio Period — PageController + Página Vue
  - [x] 16.1 Criar `PeriodPageController` em `app/Domain/Period/Controllers/` (apenas index — visualização de dashboard)
  - [x] 16.2 Criar arquivo de rotas web `app/Domain/Period/Routes/web.php`
  - [x] 16.3 Criar página `Index.vue` em `resources/js/pages/finance/periods/`

## Fase 5: Testes

- [ ] 17. Testes de backend
  - [ ] 17.1 Criar testes de feature para `TransactionService` (criação, atualização, exclusão, marcação como pago/pendente, listagem com filtros, busca por description)
  - [ ] 17.2 Criar testes de feature para `TransferService` (criação com atualização de saldo, exclusão com reversão, listagem)
  - [ ] 17.3 Criar testes de feature para `CreditCardChargeService` (criação com parcelas, validação de valores, rejeição de parcelas inválidas)
  - [ ] 17.4 Criar testes para o comando `MarkOverdueTransactionsCommand` (atualização de status, log de contagem)
  - [ ] 17.5 Criar testes para `CreateDefaultCategoriesListener` (criação no primeiro login, skip quando já existem categorias)
  - [ ] 17.6 Criar testes para API Resources (campos expostos, relacionamentos nested)
