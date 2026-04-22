# Documento de Requisitos

## Introdução

Refatoração abrangente da nomenclatura de banco de dados e estrutura de migrations do projeto Himel App. O projeto utiliza o prefixo `financial_` em todas as tabelas de domínio financeiro, o que é redundante dado que a aplicação inteira é de gestão financeira. Além disso, existem migrations incrementais que adicionam colunas individualmente, as quais devem ser consolidadas em migrations únicas por tabela, já que o projeto ainda não está em produção.

A refatoração deve propagar as mudanças de nomenclatura por toda a codebase: Models, relationships, factories, seeders, Form Requests (regras de validação), Services, testes PHPUnit, testes E2E e a coluna `financial_transaction_uid` na tabela de installments.

## Glossário

- **Sistema**: A aplicação Himel App como um todo (backend Laravel + frontend Vue/Inertia)
- **Migration**: Arquivo PHP Laravel que define a estrutura de uma tabela no banco de dados
- **Migration_Consolidada**: Migration única que contém toda a definição de uma tabela, incluindo colunas que antes estavam em migrations incrementais separadas
- **Migration_Incremental**: Migration que apenas adiciona/altera colunas em uma tabela já existente
- **Model**: Classe Eloquent que representa uma tabela do banco de dados
- **Factory**: Classe Laravel que gera dados fictícios para testes
- **Seeder**: Classe Laravel que popula o banco de dados com dados iniciais ou de teste
- **Tabela_Prefixada**: Tabela cujo nome contém o prefixo `financial_` (ex: `financial_transfers`)
- **Tabela_Limpa**: Tabela cujo nome não contém o prefixo `financial_` (ex: `transfers`)
- **Coluna_Prefixada**: Coluna cujo nome contém o prefixo `financial_` (ex: `financial_transaction_uid`)
- **Coluna_Limpa**: Coluna cujo nome não contém o prefixo `financial_` (ex: `transaction_uid`)
- **Wayfinder**: Plugin Laravel que gera rotas e actions tipadas para o frontend TypeScript
- **Form_Request**: Classe Laravel que valida dados de entrada com regras declarativas
- **Rota_Prefixada**: Rota cujo nome contém o prefixo `finance.` (ex: `finance.credit-cards.index`)
- **Rota_Limpa**: Rota cujo nome não contém o prefixo `finance.` (ex: `credit-cards.index`)
- **PageController**: Controller Laravel que renderiza páginas Inertia via `Inertia::render()`
- **Página_Inertia**: Componente Vue em `resources/js/pages/` que é renderizado pelo Inertia.js

## Requisitos

### Requisito 1: Remoção do prefixo `financial_` dos nomes de tabelas

**User Story:** Como desenvolvedor, quero que os nomes das tabelas não contenham o prefixo redundante `financial_`, para que a nomenclatura do banco de dados seja limpa e consistente.

#### Critérios de Aceitação

1. THE Sistema SHALL renomear as seguintes tabelas removendo o prefixo `financial_`:
   - `financial_accounts` → `accounts`
   - `financial_categories` → `categories`
   - `financial_periods` → `periods`
   - `financial_credit_cards` → `credit_cards`
   - `financial_transfers` → `transfers`
   - `financial_fixed_expenses` → `fixed_expenses`
   - `financial_credit_card_charges` → `credit_card_charges`
   - `financial_transactions` → `transactions`
   - `financial_credit_card_installments` → `credit_card_installments`

2. THE Sistema SHALL renomear a coluna `financial_transaction_uid` para `transaction_uid` na tabela `credit_card_installments`

3. WHEN o banco de dados for recriado via `migrate:fresh`, THE Sistema SHALL criar todas as tabelas com os nomes limpos (sem prefixo `financial_`)

### Requisito 2: Consolidação de migrations incrementais

**User Story:** Como desenvolvedor, quero que cada tabela tenha uma única migration de criação contendo todas as colunas, para que a estrutura do banco de dados seja clara e fácil de entender.

#### Critérios de Aceitação

1. THE Sistema SHALL consolidar as seguintes migrations incrementais na migration de criação da tabela correspondente:
   - `add_period_uid_to_financial_transactions` → incorporar `period_uid` na migration `create_transactions_table`
   - `add_description_to_financial_transactions_table` → incorporar `description` na migration `create_transactions_table`
   - `add_closing_day_and_last_four_digits_to_financial_credit_cards_table` → incorporar `closing_day` e `last_four_digits` na migration `create_credit_cards_table`
   - `add_purchase_date_to_financial_credit_card_charges_table` → incorporar `purchase_date` na migration `create_credit_card_charges_table`
   - `make_category_uid_nullable_in_financial_transactions` → incorporar `category_uid` como nullable na migration `create_transactions_table`

2. THE Sistema SHALL remover os 5 arquivos de migrations incrementais após a consolidação

3. THE Sistema SHALL manter a ordem de dependência entre tabelas nas migrations consolidadas (ex: `accounts` antes de `transfers`, `categories` antes de `fixed_expenses`)

4. THE Sistema SHALL atualizar todas as referências de foreign keys nas migrations consolidadas para usar os nomes de tabelas limpos

### Requisito 3: Atualização dos Models Eloquent

**User Story:** Como desenvolvedor, quero que os Models reflitam os novos nomes de tabelas e colunas, para que o ORM funcione corretamente com a nova estrutura.

#### Critérios de Aceitação

1. THE Sistema SHALL atualizar a propriedade `$table` nos seguintes Models para usar o nome limpo:
   - `Account` → `protected $table = 'accounts'`
   - `Category` → `protected $table = 'categories'`
   - `Period` → `protected $table = 'periods'`
   - `CreditCard` → `protected $table = 'credit_cards'`
   - `Transfer` → `protected $table = 'transfers'`
   - `FixedExpense` → `protected $table = 'fixed_expenses'`
   - `CreditCardCharge` → `protected $table = 'credit_card_charges'`
   - `Transaction` → `protected $table = 'transactions'`
   - `CreditCardInstallment` → `protected $table = 'credit_card_installments'`

2. THE Sistema SHALL atualizar o Model `CreditCardInstallment` para usar `transaction_uid` em vez de `financial_transaction_uid` no array `$fillable` e no relationship `transaction()`

3. WHEN o Laravel resolver o nome da tabela via Model, THE Sistema SHALL retornar o nome limpo para todos os 9 Models

### Requisito 4: Renomeação de Factories

**User Story:** Como desenvolvedor, quero que as factories sigam a convenção de nomenclatura sem o prefixo `Financial`, para manter consistência com os Models.

#### Critérios de Aceitação

1. THE Sistema SHALL renomear os seguintes arquivos de factory:
   - `FinancialAccountFactory.php` → `AccountFactory.php`
   - `FinancialCategoryFactory.php` → `CategoryFactory.php`
   - `FinancialPeriodFactory.php` → `PeriodFactory.php`
   - `FinancialCreditCardFactory.php` → `CreditCardFactory.php`
   - `FinancialTransferFactory.php` → `TransferFactory.php`
   - `FinancialFixedExpenseFactory.php` → `FixedExpenseFactory.php`
   - `FinancialCreditCardChargeFactory.php` → `CreditCardChargeFactory.php`
   - `FinancialTransactionFactory.php` → `TransactionFactory.php`
   - `FinancialCreditCardInstallmentFactory.php` → `CreditCardInstallmentFactory.php`

2. THE Sistema SHALL atualizar os nomes das classes dentro de cada factory para corresponder ao novo nome do arquivo

3. THE Sistema SHALL atualizar todas as referências às factories nos Models (PHPDoc `@use HasFactory<...>` e imports)

4. THE Sistema SHALL atualizar todas as referências às factories no `E2eTestSeeder` e em qualquer outro arquivo que as importe

### Requisito 5: Renomeação de Seeders

**User Story:** Como desenvolvedor, quero que os seeders sigam a convenção de nomenclatura sem o prefixo `Financial`, para manter consistência com o restante do projeto.

#### Critérios de Aceitação

1. THE Sistema SHALL renomear os seguintes arquivos de seeder:
   - `FinancialAccountSeeder.php` → `AccountSeeder.php`
   - `FinancialCategorySeeder.php` → `CategorySeeder.php`
   - `FinancialPeriodSeeder.php` → `PeriodSeeder.php`
   - `FinancialCreditCardSeeder.php` → `CreditCardSeeder.php`
   - `FinancialTransferSeeder.php` → `TransferSeeder.php`
   - `FinancialFixedExpenseSeeder.php` → `FixedExpenseSeeder.php`
   - `FinancialCreditCardChargeSeeder.php` → `CreditCardChargeSeeder.php`
   - `FinancialTransactionSeeder.php` → `TransactionSeeder.php`
   - `FinancialCreditCardInstallmentSeeder.php` → `CreditCardInstallmentSeeder.php`
   - `FinancialSeeder.php` → `FinanceSeeder.php` (seeder orquestrador)

2. THE Sistema SHALL atualizar os nomes das classes dentro de cada seeder para corresponder ao novo nome do arquivo

3. THE Sistema SHALL atualizar o `DatabaseSeeder` para referenciar `FinanceSeeder` em vez de `FinancialSeeder`

4. THE Sistema SHALL atualizar o `FinanceSeeder` (antigo `FinancialSeeder`) para referenciar os novos nomes de seeders

### Requisito 6: Atualização de Form Requests e regras de validação

**User Story:** Como desenvolvedor, quero que as regras de validação referenciem os nomes corretos das tabelas, para que a validação `exists:` funcione com a nova estrutura.

#### Critérios de Aceitação

1. THE Sistema SHALL atualizar a regra de validação em `StoreTransactionRequest` de `exists:financial_periods,uid` para `exists:periods,uid`

2. WHEN qualquer Form Request contiver regras `exists:` ou `unique:` referenciando tabelas com prefixo `financial_`, THE Sistema SHALL atualizar para usar o nome limpo da tabela

### Requisito 7: Atualização de Services

**User Story:** Como desenvolvedor, quero que os Services referenciem os nomes corretos de colunas, para que as operações de banco de dados funcionem corretamente.

#### Critérios de Aceitação

1. THE Sistema SHALL atualizar o `CreditCardChargeService` para usar `transaction_uid` em vez de `financial_transaction_uid` ao criar installments

2. THE Sistema SHALL atualizar o `PeriodService` para usar `transaction_uid` em vez de `financial_transaction_uid` ao acessar a propriedade do installment

3. WHEN qualquer Service referenciar a coluna `financial_transaction_uid`, THE Sistema SHALL atualizar para `transaction_uid`

### Requisito 8: Atualização dos testes PHPUnit

**User Story:** Como desenvolvedor, quero que os testes referenciem os nomes corretos de tabelas e colunas, para que continuem passando após a refatoração.

#### Critérios de Aceitação

1. THE Sistema SHALL atualizar todas as chamadas `assertDatabaseHas`, `assertDatabaseMissing`, `assertDatabaseCount` e `DB::table()` nos testes para usar os nomes de tabelas limpos

2. THE Sistema SHALL atualizar todas as referências à coluna `financial_transaction_uid` nos testes para `transaction_uid`

3. THE Sistema SHALL atualizar os seguintes arquivos de teste:
   - `tests/Feature/PeriodInitializationTest.php`
   - `tests/Feature/PeriodCreationAndDeletionTest.php`
   - `tests/Feature/PeriodViewAndFilterTest.php`
   - `tests/Feature/PeriodTransactionManagementTest.php`
   - `tests/Feature/Domain/Period/PeriodServiceInitializeTest.php`
   - `tests/Feature/Domain/Period/PeriodServiceDeleteTest.php`
   - `tests/Feature/Domain/Period/PeriodServiceCreateTest.php`
   - `tests/Feature/Domain/Category/CreateDefaultCategoriesListenerTest.php`
   - `tests/Feature/Domain/Transaction/MarkOverdueTransactionsCommandTest.php`

4. WHEN todos os testes forem executados via `php artisan test`, THE Sistema SHALL ter 0 falhas

### Requisito 9: Atualização do E2eTestSeeder

**User Story:** Como desenvolvedor, quero que o seeder de testes E2E use as factories e referências corretas, para que os testes E2E continuem funcionando.

#### Critérios de Aceitação

1. THE Sistema SHALL atualizar todos os imports de factories no `E2eTestSeeder` para usar os novos nomes (sem prefixo `Financial`)

2. THE Sistema SHALL atualizar todas as chamadas de factory no `E2eTestSeeder` (ex: `FinancialAccountFactory::new()` → `AccountFactory::new()`)

### Requisito 10: Verificação de integridade pós-refatoração

**User Story:** Como desenvolvedor, quero garantir que toda a aplicação funcione corretamente após a refatoração, para que não haja regressões.

#### Critérios de Aceitação

1. WHEN `php artisan migrate:fresh --seed` for executado, THE Sistema SHALL completar sem erros

2. WHEN `php artisan test --compact` for executado, THE Sistema SHALL ter 0 falhas em todos os testes

3. WHEN `vendor/bin/pint --dirty --format agent` for executado, THE Sistema SHALL reportar 0 erros de formatação

4. WHEN `npm run types:check` for executado, THE Sistema SHALL reportar 0 erros de tipo TypeScript

5. IF alguma referência ao prefixo `financial_` permanecer em arquivos PHP (exceto migrations de sistema como `personal_access_tokens`), THEN THE Sistema SHALL identificar e corrigir a referência

### Requisito 11: Remoção do prefixo `finance.` dos nomes de rotas e do prefixo de URL `/finance`

**User Story:** Como desenvolvedor, quero que as rotas não contenham o prefixo redundante `finance.` nos nomes nem `/finance` na URL, para que a nomenclatura de rotas seja limpa e consistente com a remoção do prefixo `financial_` do banco de dados.

#### Critérios de Aceitação

1. THE Sistema SHALL remover o grupo `Route::prefix('finance')->name('finance.')` do arquivo `routes/web.php`, movendo todas as rotas de domínio diretamente para dentro do grupo `auth + verified`

2. THE Sistema SHALL remover o prefixo `finance.` dos nomes de rotas nos seguintes arquivos de domínio:
   - `app/Domain/CreditCard/Routes/web.php`: `->names('finance.credit-cards')` → `->names('credit-cards')`
   - `app/Domain/Category/Routes/web.php`: `->names('finance.categories')` → `->names('categories')`
   - `app/Domain/Period/Routes/web.php`: `->names('finance.periods')` → `->names('periods')` e todas as rotas nomeadas `finance.periods.*`
   - `app/Domain/FixedExpense/Routes/web.php`: `->names('finance.fixed-expenses')` → `->names('fixed-expenses')`
   - `app/Domain/CreditCardCharge/Routes/web.php`: `->names('finance.credit-card-charges')` → `->names('credit-card-charges')`

3. THE Sistema SHALL manter os nomes de rotas dos domínios que já não usam o prefixo `finance.`:
   - `app/Domain/Account/Routes/web.php`: `->names('accounts')` (sem alteração)
   - `app/Domain/Transaction/Routes/web.php`: `->names('transactions')` (sem alteração)
   - `app/Domain/Transfer/Routes/web.php`: `->names('transfers')` (sem alteração)

4. THE Sistema SHALL atualizar todas as chamadas `redirect()->route('finance.*')` nos Controllers para usar os novos nomes de rota sem o prefixo `finance.` (ex: `finance.accounts.index` → `accounts.index`, `finance.credit-cards.index` → `credit-cards.index`)

5. WHEN `php artisan route:list` for executado, THE Sistema SHALL listar todas as rotas de domínio sem o prefixo `finance.` nos nomes e sem `/finance` no path da URL

### Requisito 12: Reestruturação do diretório de páginas frontend

**User Story:** Como desenvolvedor, quero que as páginas Inertia não fiquem dentro do subdiretório `finance/`, para que a estrutura de diretórios reflita a remoção do prefixo `finance` do projeto.

#### Critérios de Aceitação

1. THE Sistema SHALL mover todas as páginas de `resources/js/pages/finance/<domínio>/` para `resources/js/pages/<domínio>/`:
   - `resources/js/pages/finance/accounts/Index.vue` → `resources/js/pages/accounts/Index.vue`
   - `resources/js/pages/finance/categories/Index.vue` → `resources/js/pages/categories/Index.vue`
   - `resources/js/pages/finance/credit-cards/Index.vue` → `resources/js/pages/credit-cards/Index.vue`
   - `resources/js/pages/finance/credit-card-charges/Index.vue` → `resources/js/pages/credit-card-charges/Index.vue`
   - `resources/js/pages/finance/fixed-expenses/Index.vue` → `resources/js/pages/fixed-expenses/Index.vue`
   - `resources/js/pages/finance/periods/Index.vue` → `resources/js/pages/periods/Index.vue`
   - `resources/js/pages/finance/periods/Show.vue` → `resources/js/pages/periods/Show.vue`
   - `resources/js/pages/finance/transactions/Index.vue` → `resources/js/pages/transactions/Index.vue`
   - `resources/js/pages/finance/transfers/Index.vue` → `resources/js/pages/transfers/Index.vue`

2. THE Sistema SHALL mover ou remover a página `resources/js/pages/finance/Index.vue` (página índice do módulo finance)

3. THE Sistema SHALL remover o diretório `resources/js/pages/finance/` após a migração de todas as páginas

4. THE Sistema SHALL atualizar todos os imports que referenciem caminhos dentro de `pages/finance/`

### Requisito 13: Atualização das chamadas Inertia::render() nos Controllers

**User Story:** Como desenvolvedor, quero que as chamadas `Inertia::render()` apontem para os novos caminhos de páginas sem o prefixo `finance/`, para que o Inertia resolva as páginas corretamente.

#### Critérios de Aceitação

1. THE Sistema SHALL atualizar todas as chamadas `Inertia::render()` nos PageControllers para remover o prefixo `finance/`:
   - `Inertia::render('finance/accounts/Index')` → `Inertia::render('accounts/Index')`
   - `Inertia::render('finance/categories/Index')` → `Inertia::render('categories/Index')`
   - `Inertia::render('finance/credit-cards/Index')` → `Inertia::render('credit-cards/Index')`
   - `Inertia::render('finance/credit-card-charges/Index')` → `Inertia::render('credit-card-charges/Index')`
   - `Inertia::render('finance/fixed-expenses/Index')` → `Inertia::render('fixed-expenses/Index')`
   - `Inertia::render('finance/periods/Index')` → `Inertia::render('periods/Index')`
   - `Inertia::render('finance/periods/Show')` → `Inertia::render('periods/Show')`
   - `Inertia::render('finance/transactions/Index')` → `Inertia::render('transactions/Index')`
   - `Inertia::render('finance/transfers/Index')` → `Inertia::render('transfers/Index')`

2. THE Sistema SHALL atualizar a chamada `Inertia::render('finance/Index')` no `routes/web.php` ou removê-la caso a página índice do finance seja eliminada

3. WHEN qualquer PageController renderizar uma página Inertia, THE Sistema SHALL usar o caminho sem o prefixo `finance/`

### Requisito 14: Regeneração das rotas Wayfinder

**User Story:** Como desenvolvedor, quero que as rotas tipadas do Wayfinder sejam regeneradas após as mudanças de rotas, para que o frontend tenha acesso às novas URLs e nomes de rotas.

#### Critérios de Aceitação

1. WHEN todas as alterações de rotas backend forem concluídas, THE Sistema SHALL executar `php artisan wayfinder:generate` para regenerar as rotas tipadas

2. THE Sistema SHALL verificar que o diretório `resources/js/routes/` contém as rotas atualizadas sem referências ao prefixo `/finance` nas URLs

3. THE Sistema SHALL verificar que o diretório `resources/js/actions/` contém as actions atualizadas correspondentes aos novos caminhos de Controllers

4. WHEN `npm run types:check` for executado após a regeneração, THE Sistema SHALL reportar 0 erros de tipo TypeScript

### Requisito 15: Padronização dos nomes de rotas entre domínios

**User Story:** Como desenvolvedor, quero que todos os domínios sigam o mesmo padrão de nomenclatura de rotas (sem prefixo `finance.`), para eliminar a inconsistência atual onde alguns domínios usam `finance.` e outros não.

#### Critérios de Aceitação

1. THE Sistema SHALL garantir que todos os 8 domínios com rotas web usem o padrão `->names('<domínio>')` sem prefixo `finance.`:
   - `accounts` → `accounts.index`, `accounts.store`, `accounts.update`, `accounts.destroy`
   - `categories` → `categories.index`, `categories.store`, `categories.update`, `categories.destroy`
   - `credit-cards` → `credit-cards.index`, `credit-cards.store`, `credit-cards.update`, `credit-cards.destroy`
   - `credit-card-charges` → `credit-card-charges.index`, `credit-card-charges.store`
   - `fixed-expenses` → `fixed-expenses.index`, `fixed-expenses.store`, `fixed-expenses.update`, `fixed-expenses.destroy`
   - `periods` → `periods.index`, `periods.store`, `periods.show`, `periods.destroy`, `periods.initialize`, `periods.transactions.store`, `periods.transactions.detach`
   - `transactions` → `transactions.index`, `transactions.store`, `transactions.update`, `transactions.destroy`
   - `transfers` → `transfers.index`, `transfers.store`, `transfers.destroy`

2. THE Sistema SHALL atualizar todas as referências a nomes de rotas com prefixo `finance.` no frontend (componentes Vue, stores, composables, adapters) para usar os nomes sem prefixo

3. THE Sistema SHALL atualizar todas as referências a nomes de rotas com prefixo `finance.` nos testes E2E (Playwright) para usar os nomes sem prefixo

4. WHEN `php artisan route:list --name=finance` for executado, THE Sistema SHALL retornar 0 resultados (nenhuma rota com prefixo `finance.` no nome)

### Requisito 16: Atualização dos links de navegação no sidebar

**User Story:** Como desenvolvedor, quero que os links de navegação do sidebar não contenham o prefixo `/finance` na URL, para que a navegação reflita a nova estrutura de rotas sem o prefixo redundante.

#### Critérios de Aceitação

1. THE Sistema SHALL atualizar o array `financeNavItems` no componente `AppSidebar.vue` (`resources/js/domain/Shared/components/AppSidebar.vue`) para remover o prefixo `/finance` de todos os `href`:
   - `/finance` → `/`
   - `/finance/periods` → `/periods`
   - `/finance/accounts` → `/accounts`
   - `/finance/categories` → `/categories`
   - `/finance/transactions` → `/transactions`
   - `/finance/transfers` → `/transfers`
   - `/finance/fixed-expenses` → `/fixed-expenses`
   - `/finance/credit-cards` → `/credit-cards`
   - `/finance/credit-card-charges` → `/credit-card-charges`

2. WHEN o usuário clicar em qualquer link do sidebar, THE Sistema SHALL navegar para a URL correta sem o prefixo `/finance`

3. IF algum outro componente frontend contiver links hardcoded com o prefixo `/finance`, THEN THE Sistema SHALL atualizar para usar a URL sem prefixo

### Requisito 17: Atualização dos Page Objects dos testes E2E

**User Story:** Como desenvolvedor, quero que os Page Objects dos testes E2E usem as URLs corretas sem o prefixo `/finance`, para que os testes naveguem para as páginas corretas após a refatoração.

#### Critérios de Aceitação

1. THE Sistema SHALL atualizar o método `goto()` nos seguintes Page Objects para remover o prefixo `/finance` da URL:
   - `e2e/pages/AccountPage.ts`: `/finance/accounts` → `/accounts`
   - `e2e/pages/CreditCardPage.ts`: `/finance/credit-cards` → `/credit-cards`
   - `e2e/pages/CreditCardChargePage.ts`: `/finance/credit-card-charges` → `/credit-card-charges`
   - `e2e/pages/TransferPage.ts`: `/finance/transfers` → `/transfers`
   - `e2e/pages/FixedExpensePage.ts`: `/finance/fixed-expenses` → `/fixed-expenses`
   - `e2e/pages/TransactionPage.ts`: `/finance/transactions` → `/transactions`

2. IF algum Page Object adicional contiver URLs hardcoded com o prefixo `/finance`, THEN THE Sistema SHALL atualizar para usar a URL sem prefixo

3. WHEN os testes E2E forem executados via `npm run test:e2e`, THE Sistema SHALL navegar corretamente para todas as páginas sem o prefixo `/finance`
