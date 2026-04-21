# Tarefas de Implementação — E2E FixedExpense

## Tarefa 1: Correção da Factory FinancialFixedExpenseFactory
- [x] 1.1 Adicionar `protected $model = FixedExpense::class` à classe `FinancialFixedExpenseFactory` em `database/factories/FinancialFixedExpenseFactory.php`

## Tarefa 2: Atualização do E2eTestSeeder com dados FixedExpense
- [x] 2.1 Adicionar imports de `Category`, `FixedExpense` e `FinancialFixedExpenseFactory` ao `E2eTestSeeder.php`
- [x] 2.2 Adicionar método `resetFixedExpenses(User $user)` que deleta todos os FixedExpense do usuário
- [x] 2.3 Adicionar método `seedNamedFixedExpenses(User $user)` que cria 3 registros: "Aluguel" (1500.00, dia 10, ativa), "Internet" (120.00, dia 15, ativa), "Academia" (89.90, dia 5, inativa) — todos com a primeira categoria OUTFLOW do usuário
- [x] 2.4 Adicionar método `seedFactoryFixedExpenses(User $user)` que cria 20 registros via factory com `user_uid` e `category_uid` OUTFLOW do usuário
- [x] 2.5 Chamar `resetFixedExpenses`, `seedNamedFixedExpenses` e `seedFactoryFixedExpenses` no método `run()` após os blocos de CreditCardCharge

## Tarefa 3: Dialog Sync Fix no FixedExpense Index.vue
- [~] 3.1 Adicionar função `handleDialogOpenChange(open: boolean)` que chama `store.closeModal()` quando `open` é `false` e `store.isModalOpen` é `true`
- [~] 3.2 Adicionar `@update:open="handleDialogOpenChange"` ao `<ModalDialog>` no template

## Tarefa 4: Criar Page Object FixedExpensePage.ts
- [~] 4.1 Criar arquivo `e2e/pages/FixedExpensePage.ts` com interface `FixedExpenseFormData` (description, amount, due_day, category_uid, active) e classe `FixedExpensePage`
- [~] 4.2 Implementar métodos de navegação: `goto()` (URL `/finance/fixed-expenses`), `getPageTitle()` (heading "Despesas Fixas")
- [~] 4.3 Implementar métodos de DataTable: `getTableRows()`, `getRowByDescription(desc)`, `getEmptyState()`
- [~] 4.4 Implementar métodos de busca: `search(term)`, `clearSearch()` — com `waitForResponse` usando URL pattern `fixed-expenses`
- [~] 4.5 Implementar métodos de paginação: `getNextButton()`, `getPreviousButton()`, `goToNextPage()`, `goToPreviousPage()`
- [~] 4.6 Implementar métodos de CRUD modal: `clickCreateButton()`, `clickEditButton(desc)`, `clickViewButton(desc)`, `clickDeleteButton(desc)`
- [~] 4.7 Implementar métodos de modal: `getModalTitle()`, `isModalOpen()`, `submitForm()`, `cancelForm()`
- [~] 4.8 Implementar `fillForm(data)` com tratamento especial para: category_uid (combobox Select) e active (checkbox com lógica condicional)
- [~] 4.9 Implementar `getFormFieldValue(field)` e `isFieldDisabled(field)` com tratamento para category_uid (combobox) e active (checkbox)
- [~] 4.10 Implementar métodos de diálogo: `closeDialogByEsc()`, `closeDialogByOverlay()`
- [~] 4.11 Implementar `confirmDelete()`, `waitForToast(message)`, `getValidationError(field)` com mapeamento de labels

## Tarefa 5: Criar spec de testes fixed-expense.spec.ts
- [~] 5.1 Criar arquivo `e2e/tests/fixed-expense.spec.ts` com bloco "FixedExpense Listing" (3 testes: título da página, registros semeados visíveis, conteúdo das linhas com valor formatado e Badge de status)
- [~] 5.2 Adicionar bloco "FixedExpense Search and Filtering" (3 testes: busca filtra, limpar retorna todos, busca sem resultado mostra empty state)
- [~] 5.3 Adicionar bloco "FixedExpense Pagination" (5 testes: controles visíveis, próxima, anterior, anterior desabilitado, próxima desabilitado na última)
- [~] 5.4 Adicionar bloco "FixedExpense Dialog Reopen" (2 testes: reabertura via ESC, reabertura via overlay)
- [~] 5.5 Adicionar bloco "FixedExpense Creation" (5 testes: modal abre, submit com sucesso mostra toast, novo registro na DataTable, validação de erros, cancelar fecha modal)
- [~] 5.6 Adicionar bloco "FixedExpense Editing" (4 testes: modal abre com título, campos pré-preenchidos, modificação mostra toast, DataTable reflete atualização)
- [~] 5.7 Adicionar bloco "FixedExpense Viewing" (3 testes: modal abre com título, campos desabilitados incluindo active, sem botão submit)
- [~] 5.8 Adicionar bloco "FixedExpense Deletion" (3 testes: popover de confirmação, confirmação mostra toast, registro removido da DataTable)
