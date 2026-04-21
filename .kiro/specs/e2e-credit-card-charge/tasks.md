# Tarefas de Implementação — Testes E2E para CreditCardCharge

## Tarefa 1: Atualizar Seeder com dados de CreditCardCharge
- [x] 1.1 Adicionar imports de `CreditCardCharge`, `CreditCardInstallment` e `FinancialCreditCardChargeFactory` no `E2eTestSeeder.php`
- [x] 1.2 Criar método `resetCreditCardCharges(User $user)` que deleta installments e charges associados aos cartões do usuário (respeitar FK: installments antes de charges)
- [x] 1.3 Criar método `seedNamedCreditCardCharges(User $user)` que cria 3 compras nomeadas: "Notebook Dell" (R$4500, 12x, Nubank), "Fone Bluetooth" (R$250, 3x, Inter), "Curso Online" (R$1200, 6x, C6 Bank)
- [x] 1.4 Criar método `seedFactoryCreditCardCharges(User $user)` que cria 13 compras via factory distribuídas entre os 3 cartões nomeados (total 16 > 15 per_page)
- [x] 1.5 Chamar os novos métodos no `run()` após o seeding de cartões: `resetCreditCardCharges`, `seedNamedCreditCardCharges`, `seedFactoryCreditCardCharges`

## Tarefa 2: Criar Page Object `CreditCardChargePage`
- [~] 2.1 Criar arquivo `e2e/pages/CreditCardChargePage.ts` com a interface `CreditCardChargeFormData` (credit_card_uid, description, amount, total_installments)
- [~] 2.2 Implementar métodos de navegação: `goto()` navega para `/finance/credit-card-charges` e aguarda tabela visível
- [~] 2.3 Implementar métodos de DataTable: `getPageTitle()`, `getTableRows()`, `getRowByDescription(desc)`, `getEmptyState()`
- [~] 2.4 Implementar métodos de busca: `search(term)` e `clearSearch()` com `waitForResponse` para `credit-card-charges`
- [~] 2.5 Implementar métodos de paginação: `getNextButton()`, `getPreviousButton()`, `goToNextPage()`, `goToPreviousPage()`
- [~] 2.6 Implementar métodos de modal: `clickCreateButton()`, `clickViewButton(desc)`, `clickEditButton(desc)`, `clickDeleteButton(desc)`, `getModalTitle()`, `isModalOpen()`
- [~] 2.7 Implementar métodos de formulário: `fillForm(data)` (com tratamento especial do select de cartão via combobox/option), `submitForm()`, `cancelForm()`, `getFormFieldValue(field)`, `isFieldDisabled(field)`, `isSubmitButtonVisible()`
- [~] 2.8 Implementar métodos auxiliares: `confirmDelete()`, `waitForToast(message)`, `getValidationError(field)` com labelMap para campos do CreditCardCharge

## Tarefa 3: Criar spec de testes `credit-card-charge.spec.ts`
- [~] 3.1 Criar arquivo `e2e/tests/credit-card-charge.spec.ts` com imports do Playwright e do `CreditCardChargePage`
- [~] 3.2 Implementar bloco `CreditCardCharge Listing` (3 testes): título da página, registros semeados visíveis, colunas da tabela (descrição, valor formatado, parcelas, cartão)
- [~] 3.3 Implementar bloco `CreditCardCharge Search and Filtering` (3 testes): filtro por descrição, limpar busca, busca sem resultados
- [~] 3.4 Implementar bloco `CreditCardCharge Pagination` (5 testes): controles visíveis, próxima página, página anterior, "Anterior" desabilitado na primeira, "Próxima" desabilitado na última
- [~] 3.5 Implementar bloco `CreditCardCharge Creation` (5 testes): modal abre com título "Nova Compra", submit com sucesso + toast, novo registro na tabela, validação de campos vazios, cancelar fecha modal
- [~] 3.6 Implementar bloco `CreditCardCharge Viewing` (3 testes): modal abre com título "Detalhes da Compra", campos desabilitados, sem botão submit
- [~] 3.7 Implementar bloco `CreditCardCharge Editing` com `test.describe.skip` (4 testes): modal abre com título "Editar Compra", campos pré-preenchidos, submit com sucesso + toast, tabela atualizada
- [~] 3.8 Implementar bloco `CreditCardCharge Deletion` com `test.describe.skip` (3 testes): popover "Tem certeza?", toast de sucesso, registro removido da tabela
