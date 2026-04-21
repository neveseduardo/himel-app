# Documento de Requisitos — E2E FixedExpense

## Introdução

Este documento descreve os requisitos para implementação de testes E2E (Playwright) cobrindo o CRUD completo do módulo FixedExpense (Despesas Fixas). Inclui a criação do Page Object, spec de testes, atualização do seeder, correção da factory e aplicação do dialog sync fix — replicando o padrão já estabelecido nos módulos CreditCard e CreditCardCharge.

## Glossário

- **Sistema**: Aplicação Himel App (Laravel + Inertia + Vue)
- **FixedExpense_Index**: Página Vue `resources/js/pages/finance/fixed-expenses/Index.vue`
- **ModalDialog**: Componente `resources/js/domain/Shared/components/ui/modal/ModalDialog.vue`
- **FixedExpenseStore**: Store Pinia `useFixedExpenseStore`
- **FixedExpenseForm**: Componente Vue `resources/js/domain/FixedExpense/components/FixedExpenseForm.vue`
- **DataTable**: Componente de tabela de dados com colunas: Descrição, Valor, Dia Venc., Status, Ações
- **PageObject**: Classe Playwright `e2e/pages/FixedExpensePage.ts`
- **E2E_Spec**: Arquivo de testes Playwright `e2e/tests/fixed-expense.spec.ts`
- **E2eTestSeeder**: Seeder `database/seeders/E2eTestSeeder.php`
- **FinancialFixedExpenseFactory**: Factory `database/factories/FinancialFixedExpenseFactory.php`
- **FilterBar**: Componente de busca que filtra pelo campo `name` no backend

## Requisitos

### Requisito 1: Correção da Factory FinancialFixedExpenseFactory

**User Story:** Como desenvolvedor, eu quero que a factory FinancialFixedExpenseFactory funcione corretamente com o model FixedExpense, para que o seeder E2E consiga gerar registros de volume para testes de paginação.

#### Critérios de Aceitação

1. THE FinancialFixedExpenseFactory SHALL definir `protected $model = FixedExpense::class` para resolver o mismatch de nomenclatura entre a factory (`FinancialFixedExpenseFactory`) e o model (`FixedExpense`)
2. WHEN a factory é invocada com `FinancialFixedExpenseFactory::new()->count(20)->create(...)`, THE FinancialFixedExpenseFactory SHALL criar 20 registros válidos na tabela `financial_fixed_expenses`

### Requisito 2: Seed de Dados FixedExpense no E2eTestSeeder

**User Story:** Como desenvolvedor, eu quero que o E2eTestSeeder inclua dados de FixedExpense (3 nomeados + 20 de factory), para que os testes E2E tenham dados previsíveis para validação e volume suficiente para paginação.

#### Critérios de Aceitação

1. WHEN o E2eTestSeeder executa, THE E2eTestSeeder SHALL remover todos os registros FixedExpense do usuário E2E antes de re-seed (idempotência)
2. THE E2eTestSeeder SHALL criar 3 registros nomeados: "Aluguel" (amount: 1500.00, due_day: 10, active: true), "Internet" (amount: 120.00, due_day: 15, active: true), "Academia" (amount: 89.90, due_day: 5, active: false)
3. THE E2eTestSeeder SHALL associar cada registro nomeado à primeira categoria com direction "OUTFLOW" do usuário E2E
4. THE E2eTestSeeder SHALL criar 20 registros adicionais via FinancialFixedExpenseFactory para testes de paginação
5. THE E2eTestSeeder SHALL atribuir uma categoria OUTFLOW válida do usuário E2E aos registros de factory

### Requisito 3: Dialog Sync Fix para FixedExpense Index.vue

**User Story:** Como usuário, eu quero que o modal de despesa fixa reabra corretamente após ser fechado via tecla ESC ou clique no overlay, para que eu não precise recarregar a página.

#### Critérios de Aceitação

1. WHEN o ModalDialog emite `update:open` com valor `false`, THE FixedExpense_Index SHALL chamar `store.closeModal()` para sincronizar o estado do FixedExpenseStore
2. WHEN o usuário fecha o modal via tecla ESC e clica em "Criar" novamente, THE FixedExpense_Index SHALL exibir o modal com título "Nova Despesa Fixa"
3. WHEN o usuário fecha o modal via clique no overlay e clica em "Criar" novamente, THE FixedExpense_Index SHALL exibir o modal com título "Nova Despesa Fixa"

### Requisito 4: Page Object FixedExpensePage

**User Story:** Como desenvolvedor, eu quero um Page Object Playwright para o módulo FixedExpense, para que os testes E2E sejam organizados, reutilizáveis e sigam o padrão dos módulos existentes.

#### Critérios de Aceitação

1. THE PageObject SHALL navegar para `/finance/fixed-expenses` e aguardar a tabela ficar visível
2. THE PageObject SHALL fornecer método `getPageTitle()` que retorna o texto do heading "Despesas Fixas"
3. THE PageObject SHALL fornecer métodos de DataTable: `getTableRows()`, `getRowByDescription(desc)`, `getEmptyState()`
4. THE PageObject SHALL fornecer métodos de busca: `search(term)` e `clearSearch()` que aguardam resposta HTTP contendo "fixed-expenses"
5. THE PageObject SHALL fornecer métodos de paginação: `getNextButton()`, `getPreviousButton()`, `goToNextPage()`, `goToPreviousPage()`
6. THE PageObject SHALL fornecer métodos de CRUD modal: `clickCreateButton()`, `clickEditButton(desc)`, `clickViewButton(desc)`, `clickDeleteButton(desc)`
7. THE PageObject SHALL fornecer método `fillForm(data)` que preenche os campos: description (input text), amount (input number), due_day (input number), category_uid (reka-ui Select combobox), active (checkbox)
8. THE PageObject SHALL fornecer métodos de modal: `getModalTitle()`, `isModalOpen()`, `submitForm()`, `cancelForm()`
9. THE PageObject SHALL fornecer métodos de formulário: `getFormFieldValue(field)`, `isFieldDisabled(field)`, `isSubmitButtonVisible()`
10. THE PageObject SHALL fornecer métodos de diálogo: `closeDialogByEsc()`, `closeDialogByOverlay()`
11. THE PageObject SHALL fornecer métodos de deleção: `confirmDelete()` e toast: `waitForToast(message)`
12. THE PageObject SHALL fornecer método `getValidationError(field)` com mapeamento de labels para os campos: description→"Descrição", amount→"Valor", due_day→"Dia Vencimento", category_uid→"Categoria"

### Requisito 5: Testes E2E de Listagem de FixedExpense

**User Story:** Como desenvolvedor, eu quero testes E2E que validem a listagem de despesas fixas, para garantir que a página renderiza corretamente com os dados semeados.

#### Critérios de Aceitação

1. WHEN a página é carregada, THE E2E_Spec SHALL verificar que o título "Despesas Fixas" é exibido
2. WHEN a página é carregada, THE E2E_Spec SHALL verificar que os 3 registros nomeados ("Aluguel", "Internet", "Academia") são visíveis na DataTable via busca individual
3. THE E2E_Spec SHALL verificar que cada linha exibe: descrição, valor formatado em moeda (ex: "1.500,00"), dia de vencimento, e status ("Ativa" ou "Inativa" via Badge)

### Requisito 6: Testes E2E de Busca e Filtragem de FixedExpense

**User Story:** Como desenvolvedor, eu quero testes E2E que validem a busca e filtragem de despesas fixas, para garantir que o FilterBar funciona corretamente.

#### Critérios de Aceitação

1. WHEN um termo de busca é digitado e submetido, THE E2E_Spec SHALL verificar que a DataTable exibe apenas registros correspondentes
2. WHEN a busca é limpa, THE E2E_Spec SHALL verificar que a DataTable retorna todos os registros
3. WHEN um termo sem correspondência é buscado, THE E2E_Spec SHALL verificar que a mensagem "Nenhum registro encontrado." é exibida

### Requisito 7: Testes E2E de Paginação de FixedExpense

**User Story:** Como desenvolvedor, eu quero testes E2E que validem a paginação de despesas fixas, para garantir que a navegação entre páginas funciona com os 23 registros semeados.

#### Critérios de Aceitação

1. WHEN existem mais registros que o limite por página, THE E2E_Spec SHALL verificar que os botões "Próxima" e "Anterior" são visíveis
2. WHEN o botão "Próxima" é clicado, THE E2E_Spec SHALL verificar que a próxima página de registros é exibida
3. WHEN o botão "Anterior" é clicado após navegar para a segunda página, THE E2E_Spec SHALL verificar que a página anterior é exibida
4. WHEN o usuário está na primeira página, THE E2E_Spec SHALL verificar que o botão "Anterior" está desabilitado
5. WHEN o usuário está na última página, THE E2E_Spec SHALL verificar que o botão "Próxima" está desabilitado

### Requisito 8: Testes E2E de Reabertura do Dialog de FixedExpense

**User Story:** Como desenvolvedor, eu quero testes E2E que validem a reabertura do modal após fechamento via ESC e overlay, para garantir que o dialog sync fix funciona corretamente.

#### Critérios de Aceitação

1. WHEN o modal é fechado via tecla ESC e reaberto via botão "Criar", THE E2E_Spec SHALL verificar que o modal exibe o título "Nova Despesa Fixa"
2. WHEN o modal é fechado via clique no overlay e reaberto via botão "Criar", THE E2E_Spec SHALL verificar que o modal exibe o título "Nova Despesa Fixa"

### Requisito 9: Testes E2E de Criação de FixedExpense

**User Story:** Como desenvolvedor, eu quero testes E2E que validem a criação de despesas fixas, para garantir que o fluxo completo de criação funciona via modal.

#### Critérios de Aceitação

1. WHEN o botão "Criar" é clicado, THE E2E_Spec SHALL verificar que o modal abre com título "Nova Despesa Fixa"
2. WHEN todos os campos são preenchidos e o formulário é submetido, THE E2E_Spec SHALL verificar que o toast "Despesa fixa criado(a) com sucesso!" é exibido
3. WHEN uma despesa fixa é criada com sucesso, THE E2E_Spec SHALL verificar que o novo registro aparece na DataTable via busca
4. WHEN o formulário é submetido com dados inválidos (campos vazios), THE E2E_Spec SHALL verificar que erros de validação são exibidos
5. WHEN o botão "Cancelar" é clicado, THE E2E_Spec SHALL verificar que o modal fecha sem criar registro

### Requisito 10: Testes E2E de Edição de FixedExpense

**User Story:** Como desenvolvedor, eu quero testes E2E que validem a edição de despesas fixas, para garantir que o fluxo de edição funciona via modal.

#### Critérios de Aceitação

1. WHEN o ícone de edição é clicado em uma linha, THE E2E_Spec SHALL verificar que o modal abre com título "Editar Despesa Fixa"
2. WHEN o modal de edição abre, THE E2E_Spec SHALL verificar que os campos estão pré-preenchidos com os dados existentes
3. WHEN os campos são modificados e o formulário é submetido, THE E2E_Spec SHALL verificar que o toast "Despesa fixa atualizado(a) com sucesso!" é exibido
4. WHEN a edição é concluída com sucesso, THE E2E_Spec SHALL verificar que a DataTable reflete os dados atualizados

### Requisito 11: Testes E2E de Visualização de FixedExpense

**User Story:** Como desenvolvedor, eu quero testes E2E que validem a visualização de despesas fixas, para garantir que o modo read-only funciona corretamente.

#### Critérios de Aceitação

1. WHEN o ícone de visualização é clicado em uma linha, THE E2E_Spec SHALL verificar que o modal abre com título "Detalhes da Despesa Fixa"
2. WHEN o modal de visualização abre, THE E2E_Spec SHALL verificar que todos os campos de formulário estão desabilitados (description, amount, due_day, category_uid, active)
3. WHEN o modal de visualização abre, THE E2E_Spec SHALL verificar que o botão de submit (Criar/Salvar) não é visível

### Requisito 12: Testes E2E de Deleção de FixedExpense

**User Story:** Como desenvolvedor, eu quero testes E2E que validem a deleção de despesas fixas, para garantir que o fluxo de exclusão funciona com confirmação.

#### Critérios de Aceitação

1. WHEN o ícone de deleção é clicado em uma linha, THE E2E_Spec SHALL verificar que o popover de confirmação "Tem certeza?" é exibido
2. WHEN a deleção é confirmada, THE E2E_Spec SHALL verificar que o toast "Despesa fixa excluído(a) com sucesso!" é exibido
3. WHEN a deleção é concluída com sucesso, THE E2E_Spec SHALL verificar que o registro é removido da DataTable
