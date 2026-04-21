# Tarefas — Testes E2E: Account, Transfer e Transaction

## Fase 1: Infraestrutura Compartilhada

- [x] 1. Corrigir factories dos módulos Account, Transfer e Transaction
  - [x] 1.1 Adicionar `protected $model = Account::class` e import em `FinancialAccountFactory.php`
  - [x] 1.2 Adicionar `protected $model = Transfer::class` em `FinancialTransferFactory.php`
  - [x] 1.3 Adicionar `protected $model = Transaction::class` em `FinancialTransactionFactory.php`
- [x] 2. Atualizar E2eTestSeeder com dados para Account, Transfer e Transaction
  - [x] 2.1 Adicionar imports dos models Account, Transfer, Transaction e factories
  - [x] 2.2 Adicionar métodos `resetAccounts`, `resetTransfers`, `resetTransactions` (ordem inversa de FK)
  - [x] 2.3 Adicionar método `seedNamedAccounts` (3 contas: "Conta Corrente BB", "Poupança Nubank", "Carteira")
  - [x] 2.4 Adicionar método `seedFactoryAccounts` (20 registros via factory)
  - [x] 2.5 Adicionar método `seedNamedTransfers` (3 transferências entre contas nomeadas)
  - [x] 2.6 Adicionar método `seedFactoryTransfers` (13 registros via factory, usando contas do usuário)
  - [x] 2.7 Adicionar método `seedNamedTransactions` (3 transações: "Salário Mensal", "Supermercado", "Conta de Luz")
  - [x] 2.8 Adicionar método `seedFactoryTransactions` (20 registros via factory, usando contas e categorias do usuário)
  - [x] 2.9 Atualizar método `run()` com chamadas na ordem correta (reset: Transactions→Transfers→Accounts, seed: Accounts→Transfers→Transactions)
- [x] 3. Corrigir dialog sync nos 3 Index.vue (Account, Transfer, Transaction)
  - [x] 3.1 Adicionar `@update:open` handler no ModalDialog de `accounts/Index.vue`
  - [x] 3.2 Adicionar `@update:open` handler no ModalDialog de `transfers/Index.vue`
  - [x] 3.3 Adicionar `@update:open` handler no ModalDialog de `transactions/Index.vue`

## Fase 2: Account E2E

- [ ] 4. Criar Page Object `AccountPage.ts`
  - [ ] 4.1 Definir interface `AccountFormData` com campos `name`, `type`, `balance`
  - [ ] 4.2 Implementar navegação: `goto()` para `/finance/accounts`
  - [ ] 4.3 Implementar DataTable: `getTableRows()`, `getRowByName(name)`, `getEmptyState()`
  - [ ] 4.4 Implementar busca: `search(term)` com `waitForResponse`, `clearSearch()`
  - [ ] 4.5 Implementar paginação: `getNextButton()`, `getPreviousButton()`, `goToNextPage()`, `goToPreviousPage()`
  - [ ] 4.6 Implementar modal: `clickCreateButton()`, `clickEditButton(name)`, `clickViewButton(name)`, `clickDeleteButton(name)`
  - [ ] 4.7 Implementar formulário: `fillForm(data)` com text input (name), Select (type), number input (balance)
  - [ ] 4.8 Implementar auxiliares: `submitForm()`, `cancelForm()`, `getModalTitle()`, `isModalOpen()`, `getFormFieldValue()`, `isFieldDisabled()`, `isSubmitButtonVisible()`
  - [ ] 4.9 Implementar dialog helpers: `closeDialogByEsc()`, `closeDialogByOverlay()`
  - [ ] 4.10 Implementar exclusão e toast: `confirmDelete()`, `waitForToast(message)`, `getValidationError(field)`
- [ ] 5. Criar spec `account.spec.ts`
  - [ ] 5.1 Testes de Listing (3 testes): título da página, exibição dos 3 Named_Records, dados formatados por linha
  - [ ] 5.2 Testes de Search (3 testes): filtro por termo, limpar busca, busca sem resultado
  - [ ] 5.3 Testes de Pagination (5 testes): controles visíveis, próxima página, página anterior, "Anterior" desabilitado na primeira, "Próxima" desabilitado na última
  - [ ] 5.4 Testes de Dialog Reopen (2 testes): reabrir após ESC, reabrir após overlay click
  - [ ] 5.5 Testes de Creation (5 testes): modal abre com título correto, submit com sucesso mostra toast, nova conta aparece na tabela, validação de dados inválidos, cancelar fecha modal
  - [ ] 5.6 Testes de Editing (4 testes): modal abre com título "Editar Conta" e dados pré-preenchidos, submit mostra toast de atualização, tabela reflete dados atualizados
  - [ ] 5.7 Testes de Viewing (3 testes): modal abre com título "Detalhes da Conta", campos desabilitados, sem botão submit
  - [ ] 5.8 Testes de Deletion (3 testes): popover de confirmação, toast de exclusão, conta removida da tabela

## Fase 3: Transfer E2E

- [ ] 6. Criar Page Object `TransferPage.ts`
  - [ ] 6.1 Definir interface `TransferFormData` com campos `from_account_uid`, `to_account_uid`, `amount`, `occurred_at`, `description`
  - [ ] 6.2 Implementar navegação: `goto()` para `/finance/transfers`
  - [ ] 6.3 Implementar DataTable: `getTableRows()`, `getRowByText(text)`, `getEmptyState()`
  - [ ] 6.4 Implementar busca: `search(term)` com `waitForResponse`, `clearSearch()`
  - [ ] 6.5 Implementar paginação: `getNextButton()`, `getPreviousButton()`, `goToNextPage()`, `goToPreviousPage()`
  - [ ] 6.6 Implementar modal: `clickCreateButton()`, `clickViewButton(text)`, `clickDeleteButton(text)` — sem `clickEditButton`
  - [ ] 6.7 Implementar formulário: `fillForm(data)` com 2 Selects (contas origem/destino), number input (amount), date input (occurred_at), text input (description)
  - [ ] 6.8 Implementar auxiliares: `submitForm()`, `cancelForm()`, `getModalTitle()`, `isModalOpen()`, `getFormFieldValue()`, `isFieldDisabled()`, `isSubmitButtonVisible()`
  - [ ] 6.9 Implementar dialog helpers: `closeDialogByEsc()`, `closeDialogByOverlay()`
  - [ ] 6.10 Implementar exclusão e toast: `confirmDelete()`, `waitForToast(message)`
- [ ] 7. Criar spec `transfer.spec.ts`
  - [ ] 7.1 Testes de Listing (3 testes): título da página, exibição dos Named_Records com conta origem/destino/valor/data
  - [ ] 7.2 Testes de Search (3 testes): filtro por termo, limpar busca, busca sem resultado
  - [ ] 7.3 Testes de Pagination (5 testes): controles visíveis, próxima página, página anterior, "Anterior" desabilitado na primeira, "Próxima" desabilitado na última
  - [ ] 7.4 Testes de Dialog Reopen (2 testes): reabrir após ESC, reabrir após overlay click
  - [ ] 7.5 Testes de Creation (5 testes): modal abre com título "Nova Transferência", submit com sucesso mostra toast, nova transferência aparece na tabela, validação de dados inválidos, cancelar fecha modal
  - [ ] 7.6 Testes de Viewing (3 testes): modal abre com título "Detalhes da Transferência", campos desabilitados, sem botão submit
  - [ ] 7.7 Testes de Deletion (3 testes): popover de confirmação, toast de exclusão, transferência removida da tabela

## Fase 4: Transaction E2E

- [ ] 8. Criar Page Object `TransactionPage.ts`
  - [ ] 8.1 Definir interface `TransactionFormData` com campos `account_uid`, `category_uid`, `amount`, `direction`, `status`, `description`, `occurred_at`, `due_date`, `paid_at`
  - [ ] 8.2 Implementar navegação: `goto()` para `/finance/transactions`
  - [ ] 8.3 Implementar DataTable: `getTableRows()`, `getRowByDescription(desc)`, `getEmptyState()`
  - [ ] 8.4 Implementar busca: `search(term)` com `waitForResponse`, `clearSearch()`
  - [ ] 8.5 Implementar paginação: `getNextButton()`, `getPreviousButton()`, `goToNextPage()`, `goToPreviousPage()`
  - [ ] 8.6 Implementar modal: `clickCreateButton()`, `clickEditButton(desc)`, `clickViewButton(desc)`, `clickDeleteButton(desc)`
  - [ ] 8.7 Implementar formulário: `fillForm(data)` com 4 Selects (account, category, direction, status), number input (amount), text input (description), 3 date inputs (occurred_at, due_date, paid_at)
  - [ ] 8.8 Implementar auxiliares: `submitForm()`, `cancelForm()`, `getModalTitle()`, `isModalOpen()`, `getFormFieldValue()`, `isFieldDisabled()`, `isSubmitButtonVisible()`
  - [ ] 8.9 Implementar dialog helpers: `closeDialogByEsc()`, `closeDialogByOverlay()`
  - [ ] 8.10 Implementar exclusão e toast: `confirmDelete()`, `waitForToast(message)`, `getValidationError(field)`
- [ ] 9. Criar spec `transaction.spec.ts`
  - [ ] 9.1 Testes de Listing (3 testes): título da página, exibição dos 3 Named_Records com descrição/valor/direção/status/data
  - [ ] 9.2 Testes de Search (3 testes): filtro por termo, limpar busca, busca sem resultado
  - [ ] 9.3 Testes de Pagination (5 testes): controles visíveis, próxima página, página anterior, "Anterior" desabilitado na primeira, "Próxima" desabilitado na última
  - [ ] 9.4 Testes de Dialog Reopen (2 testes): reabrir após ESC, reabrir após overlay click
  - [ ] 9.5 Testes de Creation (5 testes): modal abre com título "Nova Transação", submit com sucesso mostra toast, nova transação aparece na tabela, validação de dados inválidos, cancelar fecha modal
  - [ ] 9.6 Testes de Editing (4 testes): modal abre com título "Editar Transação" e dados pré-preenchidos, submit mostra toast de atualização, tabela reflete dados atualizados
  - [ ] 9.7 Testes de Viewing (3 testes): modal abre com título "Detalhes da Transação", campos desabilitados, sem botão submit
  - [ ] 9.8 Testes de Deletion (3 testes): popover de confirmação, toast de exclusão, transação removida da tabela