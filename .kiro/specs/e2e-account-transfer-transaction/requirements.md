# Documento de Requisitos — Testes E2E: Account, Transfer e Transaction

## Introdução

Testes end-to-end (E2E) com Playwright para os módulos Account (Contas), Transfer (Transferências) e Transaction (Transações), unificados em uma única spec. Segue os mesmos padrões estabelecidos nos módulos CreditCard, CreditCardCharge e FixedExpense. Inclui correções de infraestrutura (factories, seeder, dialog sync) e cobertura CRUD completa para cada módulo. Transfer não possui edição (rotas `edit`/`update` excluídas das rotas web).

## Glossário

- **Account_Page**: Página Inertia em `/finance/accounts` que exibe a listagem de contas com DataTable, filtros, paginação e modal CRUD
- **Transfer_Page**: Página Inertia em `/finance/transfers` que exibe a listagem de transferências com DataTable, filtros, paginação e modal de criação/visualização
- **Transaction_Page**: Página Inertia em `/finance/transactions` que exibe a listagem de transações com DataTable, filtros, paginação e modal CRUD
- **AccountPage_PO**: Page Object Playwright (`e2e/pages/AccountPage.ts`) que centraliza seletores e interações com a Account_Page
- **TransferPage_PO**: Page Object Playwright (`e2e/pages/TransferPage.ts`) que centraliza seletores e interações com a Transfer_Page
- **TransactionPage_PO**: Page Object Playwright (`e2e/pages/TransactionPage.ts`) que centraliza seletores e interações com a Transaction_Page
- **E2eTestSeeder**: Seeder PHP (`database/seeders/E2eTestSeeder.php`) que popula o banco com dados determinísticos para testes E2E
- **ModalDialog**: Componente Vue compartilhado que renderiza dialogs modais para CRUD
- **DataTable**: Componente Vue compartilhado que renderiza tabelas com dados paginados
- **Named_Records**: Registros com dados fixos e previsíveis usados como âncoras nos testes
- **Factory_Records**: Registros gerados via Laravel Factory para garantir volume suficiente para paginação (>15 registros)
- **Dialog_Sync**: Mecanismo de sincronização entre o estado `store.isModalOpen` e o componente ModalDialog via evento `@update:open`

## Requisitos

### Requisito 1: Correção de Factories

**User Story:** Como desenvolvedor, quero que as factories dos módulos Account, Transfer e Transaction funcionem corretamente, para que o seeder E2E consiga gerar dados de volume via factory.

#### Critérios de Aceitação

1. THE FinancialAccountFactory SHALL definir a propriedade `protected $model` apontando para `App\Domain\Account\Models\Account` e importar o model corretamente
2. THE FinancialTransferFactory SHALL definir a propriedade `protected $model` apontando para `App\Domain\Transfer\Models\Transfer` e importar o model corretamente
3. THE FinancialTransactionFactory SHALL definir a propriedade `protected $model` apontando para `App\Domain\Transaction\Models\Transaction` e importar o model corretamente
4. WHEN o E2eTestSeeder executar a criação de registros via factory, THE FinancialAccountFactory SHALL gerar registros válidos na tabela `financial_accounts`
5. WHEN o E2eTestSeeder executar a criação de registros via factory, THE FinancialTransferFactory SHALL gerar registros válidos na tabela `financial_transfers`
6. WHEN o E2eTestSeeder executar a criação de registros via factory, THE FinancialTransactionFactory SHALL gerar registros válidos na tabela `financial_transactions`

### Requisito 2: Seed de Dados E2E para Account, Transfer e Transaction

**User Story:** Como desenvolvedor, quero que o E2eTestSeeder popule o banco com dados determinísticos para os três módulos, para que os testes E2E tenham dados previsíveis e volume suficiente para paginação.

#### Critérios de Aceitação

1. THE E2eTestSeeder SHALL criar 3 Named_Records de Account: "Conta Corrente BB" (CHECKING, R$ 5.000,00), "Poupança Nubank" (SAVINGS, R$ 12.000,00), "Carteira" (CASH, R$ 350,50)
2. THE E2eTestSeeder SHALL criar 20 Factory_Records de Account para o usuário de teste
3. THE E2eTestSeeder SHALL criar 3 Named_Records de Transfer referenciando as contas nomeadas: "Conta Corrente BB → Poupança Nubank" (R$ 1.000,00), "Poupança Nubank → Carteira" (R$ 200,00), "Carteira → Conta Corrente BB" (R$ 50,00)
4. THE E2eTestSeeder SHALL criar 13 Factory_Records de Transfer para totalizar mais de 15 registros (paginação)
5. THE E2eTestSeeder SHALL criar 3 Named_Records de Transaction: "Salário Mensal" (INFLOW, PAID, R$ 8.500,00), "Supermercado" (OUTFLOW, PAID, R$ 450,00), "Conta de Luz" (OUTFLOW, PENDING, R$ 180,00)
6. THE E2eTestSeeder SHALL criar 20 Factory_Records de Transaction para o usuário de teste
7. WHEN o E2eTestSeeder executar o seed, THE E2eTestSeeder SHALL respeitar a ordem de dependências FK: criar Accounts primeiro, depois Transfers, depois Transactions
8. WHEN o E2eTestSeeder executar o reset, THE E2eTestSeeder SHALL respeitar a ordem inversa de dependências FK: resetar Transactions primeiro, depois Transfers, depois Accounts
9. THE E2eTestSeeder SHALL ser idempotente, limpando dados anteriores do usuário de teste antes de re-seed

### Requisito 3: Correção de Dialog Sync nos Módulos Account, Transfer e Transaction

**User Story:** Como usuário, quero que o modal de criação/edição reabra normalmente após fechar via ESC ou clique no overlay, para que a interação com o sistema seja consistente.

#### Critérios de Aceitação

1. WHEN o usuário fechar o ModalDialog via tecla ESC na Account_Page, THE Account_Page SHALL sincronizar o estado `store.isModalOpen` para `false` via handler `@update:open`
2. WHEN o usuário fechar o ModalDialog via clique no overlay na Account_Page, THE Account_Page SHALL sincronizar o estado `store.isModalOpen` para `false` via handler `@update:open`
3. WHEN o usuário fechar o ModalDialog via tecla ESC na Transfer_Page, THE Transfer_Page SHALL sincronizar o estado `store.isModalOpen` para `false` via handler `@update:open`
4. WHEN o usuário fechar o ModalDialog via clique no overlay na Transfer_Page, THE Transfer_Page SHALL sincronizar o estado `store.isModalOpen` para `false` via handler `@update:open`
5. WHEN o usuário fechar o ModalDialog via tecla ESC na Transaction_Page, THE Transaction_Page SHALL sincronizar o estado `store.isModalOpen` para `false` via handler `@update:open`
6. WHEN o usuário fechar o ModalDialog via clique no overlay na Transaction_Page, THE Transaction_Page SHALL sincronizar o estado `store.isModalOpen` para `false` via handler `@update:open`

### Requisito 4: Page Object para Account

**User Story:** Como desenvolvedor, quero um Page Object para o módulo Account, para que os testes E2E sejam legíveis, reutilizáveis e fáceis de manter.

#### Critérios de Aceitação

1. THE AccountPage_PO SHALL expor método `goto()` que navega para `/finance/accounts` e aguarda a tabela ficar visível
2. THE AccountPage_PO SHALL expor métodos de DataTable: `getTableRows()`, `getRowByName(name)`, `getEmptyState()`
3. THE AccountPage_PO SHALL expor métodos de busca: `search(term)` com `waitForResponse` para URL contendo `accounts`, e `clearSearch()`
4. THE AccountPage_PO SHALL expor métodos de paginação: `getNextButton()`, `getPreviousButton()`, `goToNextPage()`, `goToPreviousPage()`
5. THE AccountPage_PO SHALL expor métodos de modal: `clickCreateButton()`, `clickEditButton(name)`, `clickViewButton(name)`, `clickDeleteButton(name)`
6. THE AccountPage_PO SHALL expor método `fillForm(data)` que preenche campos `name` (text input), `type` (reka-ui Select com opções "Conta Corrente", "Poupança", "Dinheiro", "Outro") e `balance` (number input)
7. THE AccountPage_PO SHALL expor métodos auxiliares: `submitForm()`, `cancelForm()`, `getModalTitle()`, `isModalOpen()`, `getFormFieldValue(field)`, `isFieldDisabled(field)`, `isSubmitButtonVisible()`
8. THE AccountPage_PO SHALL expor métodos de dialog: `closeDialogByEsc()`, `closeDialogByOverlay()`
9. THE AccountPage_PO SHALL expor métodos de exclusão: `confirmDelete()`, `waitForToast(message)`

### Requisito 5: Testes E2E para Account

**User Story:** Como desenvolvedor, quero testes E2E cobrindo CRUD completo do módulo Account, para garantir que listagem, busca, paginação, criação, edição, visualização e exclusão funcionem corretamente.

#### Critérios de Aceitação

1. WHEN a Account_Page carregar, THE DataTable SHALL exibir os 3 Named_Records de Account com nome, tipo formatado e saldo formatado em moeda
2. WHEN o usuário digitar um termo de busca e clicar "Buscar", THE DataTable SHALL filtrar para exibir apenas contas correspondentes
3. WHEN o usuário clicar "Limpar", THE DataTable SHALL retornar a exibir todas as contas
4. WHEN o usuário buscar um termo inexistente, THE DataTable SHALL exibir a mensagem "Nenhum registro encontrado."
5. WHEN existirem mais de 15 contas, THE Account_Page SHALL exibir controles de paginação "Anterior" e "Próxima"
6. WHEN o usuário estiver na primeira página, THE botão "Anterior" SHALL estar desabilitado
7. WHEN o usuário estiver na última página, THE botão "Próxima" SHALL estar desabilitado
8. WHEN o usuário fechar o modal via ESC e clicar "Criar" novamente, THE ModalDialog SHALL reabrir com título "Nova Conta"
9. WHEN o usuário fechar o modal via overlay e clicar "Criar" novamente, THE ModalDialog SHALL reabrir com título "Nova Conta"
10. WHEN o usuário clicar "Criar", THE ModalDialog SHALL abrir com título "Nova Conta"
11. WHEN o usuário preencher todos os campos e submeter, THE Account_Page SHALL exibir toast "Conta criado(a) com sucesso!"
12. WHEN uma conta for criada, THE DataTable SHALL exibir a nova conta ao buscar pelo nome
13. WHEN o usuário submeter com dados inválidos, THE formulário SHALL exibir erros de validação
14. WHEN o usuário clicar "Cancelar", THE ModalDialog SHALL fechar sem criar conta
15. WHEN o usuário clicar no ícone de edição, THE ModalDialog SHALL abrir com título "Editar Conta" e campos pré-preenchidos
16. WHEN o usuário modificar dados e submeter, THE Account_Page SHALL exibir toast "Conta atualizado(a) com sucesso!"
17. WHEN uma conta for editada, THE DataTable SHALL refletir os dados atualizados
18. WHEN o usuário clicar no ícone de visualização, THE ModalDialog SHALL abrir com título "Detalhes da Conta" e todos os campos desabilitados
19. WHEN o modal estiver em modo visualização, THE formulário SHALL ocultar o botão de submit
20. WHEN o usuário clicar no ícone de exclusão, THE Account_Page SHALL exibir popover de confirmação "Tem certeza?"
21. WHEN o usuário confirmar a exclusão, THE Account_Page SHALL exibir toast "Conta excluído(a) com sucesso!"
22. WHEN uma conta for excluída, THE DataTable SHALL remover a conta da listagem

### Requisito 6: Page Object para Transfer

**User Story:** Como desenvolvedor, quero um Page Object para o módulo Transfer, para que os testes E2E sejam legíveis, reutilizáveis e fáceis de manter.

#### Critérios de Aceitação

1. THE TransferPage_PO SHALL expor método `goto()` que navega para `/finance/transfers` e aguarda a tabela ficar visível
2. THE TransferPage_PO SHALL expor métodos de DataTable: `getTableRows()`, `getRowByText(text)`, `getEmptyState()`
3. THE TransferPage_PO SHALL expor métodos de busca: `search(term)` com `waitForResponse` para URL contendo `transfers`, e `clearSearch()`
4. THE TransferPage_PO SHALL expor métodos de paginação: `getNextButton()`, `getPreviousButton()`, `goToNextPage()`, `goToPreviousPage()`
5. THE TransferPage_PO SHALL expor métodos de modal: `clickCreateButton()`, `clickViewButton(text)`, `clickDeleteButton(text)` — sem `clickEditButton` pois Transfer não possui edição
6. THE TransferPage_PO SHALL expor método `fillForm(data)` que preenche campos `from_account_uid` e `to_account_uid` (reka-ui Select com nomes de contas), `amount` (number input), `occurred_at` (date input) e `description` (text input)
7. THE TransferPage_PO SHALL expor métodos auxiliares: `submitForm()`, `cancelForm()`, `getModalTitle()`, `isModalOpen()`, `getFormFieldValue(field)`, `isFieldDisabled(field)`, `isSubmitButtonVisible()`
8. THE TransferPage_PO SHALL expor métodos de dialog: `closeDialogByEsc()`, `closeDialogByOverlay()`
9. THE TransferPage_PO SHALL expor métodos de exclusão: `confirmDelete()`, `waitForToast(message)`

### Requisito 7: Testes E2E para Transfer

**User Story:** Como desenvolvedor, quero testes E2E cobrindo as operações disponíveis do módulo Transfer (sem edição), para garantir que listagem, busca, paginação, criação, visualização e exclusão funcionem corretamente.

#### Critérios de Aceitação

1. WHEN a Transfer_Page carregar, THE DataTable SHALL exibir os Named_Records de Transfer com conta origem, conta destino, valor formatado e data formatada
2. WHEN o usuário digitar um termo de busca e clicar "Buscar", THE DataTable SHALL filtrar para exibir apenas transferências correspondentes
3. WHEN o usuário clicar "Limpar", THE DataTable SHALL retornar a exibir todas as transferências
4. WHEN o usuário buscar um termo inexistente, THE DataTable SHALL exibir a mensagem "Nenhum registro encontrado."
5. WHEN existirem mais de 15 transferências, THE Transfer_Page SHALL exibir controles de paginação "Anterior" e "Próxima"
6. WHEN o usuário estiver na primeira página, THE botão "Anterior" SHALL estar desabilitado
7. WHEN o usuário estiver na última página, THE botão "Próxima" SHALL estar desabilitado
8. WHEN o usuário fechar o modal via ESC e clicar "Criar" novamente, THE ModalDialog SHALL reabrir com título "Nova Transferência"
9. WHEN o usuário fechar o modal via overlay e clicar "Criar" novamente, THE ModalDialog SHALL reabrir com título "Nova Transferência"
10. WHEN o usuário clicar "Criar", THE ModalDialog SHALL abrir com título "Nova Transferência"
11. WHEN o usuário preencher todos os campos e submeter, THE Transfer_Page SHALL exibir toast "Transferência criado(a) com sucesso!"
12. WHEN uma transferência for criada, THE DataTable SHALL exibir a nova transferência ao buscar
13. WHEN o usuário submeter com dados inválidos, THE formulário SHALL exibir erros de validação
14. WHEN o usuário clicar "Cancelar", THE ModalDialog SHALL fechar sem criar transferência
15. WHEN o usuário clicar no ícone de visualização, THE ModalDialog SHALL abrir com título "Detalhes da Transferência" e todos os campos desabilitados
16. WHEN o modal estiver em modo visualização, THE formulário SHALL ocultar o botão de submit
17. WHEN o usuário clicar no ícone de exclusão, THE Transfer_Page SHALL exibir popover de confirmação "Tem certeza?"
18. WHEN o usuário confirmar a exclusão, THE Transfer_Page SHALL exibir toast "Transferência excluído(a) com sucesso!"
19. WHEN uma transferência for excluída, THE DataTable SHALL remover a transferência da listagem

### Requisito 8: Page Object para Transaction

**User Story:** Como desenvolvedor, quero um Page Object para o módulo Transaction, para que os testes E2E sejam legíveis, reutilizáveis e fáceis de manter.

#### Critérios de Aceitação

1. THE TransactionPage_PO SHALL expor método `goto()` que navega para `/finance/transactions` e aguarda a tabela ficar visível
2. THE TransactionPage_PO SHALL expor métodos de DataTable: `getTableRows()`, `getRowByDescription(desc)`, `getEmptyState()`
3. THE TransactionPage_PO SHALL expor métodos de busca: `search(term)` com `waitForResponse` para URL contendo `transactions`, e `clearSearch()`
4. THE TransactionPage_PO SHALL expor métodos de paginação: `getNextButton()`, `getPreviousButton()`, `goToNextPage()`, `goToPreviousPage()`
5. THE TransactionPage_PO SHALL expor métodos de modal: `clickCreateButton()`, `clickEditButton(desc)`, `clickViewButton(desc)`, `clickDeleteButton(desc)`
6. THE TransactionPage_PO SHALL expor método `fillForm(data)` que preenche campos `account_uid` (reka-ui Select), `category_uid` (reka-ui Select), `amount` (number input), `direction` (reka-ui Select com "Entrada"/"Saída"), `status` (reka-ui Select com "Pendente"/"Pago"), `description` (text input), `occurred_at` (date input), `due_date` (date input) e `paid_at` (date input)
7. THE TransactionPage_PO SHALL expor métodos auxiliares: `submitForm()`, `cancelForm()`, `getModalTitle()`, `isModalOpen()`, `getFormFieldValue(field)`, `isFieldDisabled(field)`, `isSubmitButtonVisible()`
8. THE TransactionPage_PO SHALL expor métodos de dialog: `closeDialogByEsc()`, `closeDialogByOverlay()`
9. THE TransactionPage_PO SHALL expor métodos de exclusão: `confirmDelete()`, `waitForToast(message)`

### Requisito 9: Testes E2E para Transaction

**User Story:** Como desenvolvedor, quero testes E2E cobrindo CRUD completo do módulo Transaction, para garantir que listagem, busca, paginação, criação, edição, visualização e exclusão funcionem corretamente.

#### Critérios de Aceitação

1. WHEN a Transaction_Page carregar, THE DataTable SHALL exibir os 3 Named_Records de Transaction com descrição, valor formatado, badge de direção, badge de status e data formatada
2. WHEN o usuário digitar um termo de busca e clicar "Buscar", THE DataTable SHALL filtrar para exibir apenas transações correspondentes
3. WHEN o usuário clicar "Limpar", THE DataTable SHALL retornar a exibir todas as transações
4. WHEN o usuário buscar um termo inexistente, THE DataTable SHALL exibir a mensagem "Nenhum registro encontrado."
5. WHEN existirem mais de 15 transações, THE Transaction_Page SHALL exibir controles de paginação "Anterior" e "Próxima"
6. WHEN o usuário estiver na primeira página, THE botão "Anterior" SHALL estar desabilitado
7. WHEN o usuário estiver na última página, THE botão "Próxima" SHALL estar desabilitado
8. WHEN o usuário fechar o modal via ESC e clicar "Criar" novamente, THE ModalDialog SHALL reabrir com título "Nova Transação"
9. WHEN o usuário fechar o modal via overlay e clicar "Criar" novamente, THE ModalDialog SHALL reabrir com título "Nova Transação"
10. WHEN o usuário clicar "Criar", THE ModalDialog SHALL abrir com título "Nova Transação"
11. WHEN o usuário preencher todos os campos e submeter, THE Transaction_Page SHALL exibir toast "Transação criado(a) com sucesso!"
12. WHEN uma transação for criada, THE DataTable SHALL exibir a nova transação ao buscar pela descrição
13. WHEN o usuário submeter com dados inválidos, THE formulário SHALL exibir erros de validação
14. WHEN o usuário clicar "Cancelar", THE ModalDialog SHALL fechar sem criar transação
15. WHEN o usuário clicar no ícone de edição, THE ModalDialog SHALL abrir com título "Editar Transação" e campos pré-preenchidos
16. WHEN o usuário modificar dados e submeter, THE Transaction_Page SHALL exibir toast "Transação atualizado(a) com sucesso!"
17. WHEN uma transação for editada, THE DataTable SHALL refletir os dados atualizados
18. WHEN o usuário clicar no ícone de visualização, THE ModalDialog SHALL abrir com título "Detalhes da Transação" e todos os campos desabilitados
19. WHEN o modal estiver em modo visualização, THE formulário SHALL ocultar o botão de submit
20. WHEN o usuário clicar no ícone de exclusão, THE Transaction_Page SHALL exibir popover de confirmação "Tem certeza?"
21. WHEN o usuário confirmar a exclusão, THE Transaction_Page SHALL exibir toast "Transação excluído(a) com sucesso!"
22. WHEN uma transação for excluída, THE DataTable SHALL remover a transação da listagem