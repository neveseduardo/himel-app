# Documento de Requisitos — Testes E2E para CreditCardCharge

## Introdução

Testes end-to-end (E2E) com Playwright para o módulo CreditCardCharge (Compras de Cartão), seguindo os mesmos padrões e boas práticas estabelecidos nos testes E2E do módulo CreditCard. O objetivo é garantir cobertura CRUD completa para o módulo, incluindo listagem, busca, paginação, criação, visualização, edição e exclusão.

**Correção de bug pré-requisito:** Foi identificado um bug no componente compartilhado `ModalDialog.vue` que impede a reabertura do diálogo após ser fechado pelo reka-ui (botão X, ESC ou clique no overlay). O Requisito 0 aborda essa correção, que é pré-requisito para o funcionamento correto dos testes E2E de criação/edição/visualização tanto no módulo CreditCard quanto no CreditCardCharge.

**Nota importante sobre o estado atual do frontend:** A análise do código revelou que a página `Index.vue` do CreditCardCharge atualmente implementa apenas Criação e Visualização (sem botões de Editar e Excluir na UI). O backend (API) suporta CRUD completo. Os requisitos abaixo cobrem o CRUD completo, sendo que os requisitos de Edição e Exclusão exigirão implementação prévia dos botões correspondentes na UI antes que os testes possam ser executados.

## Glossário

- **Sistema_E2E**: Suíte de testes end-to-end Playwright para o módulo CreditCardCharge
- **Page_Object**: Classe TypeScript `CreditCardChargePage` que encapsula seletores e interações da página de Compras de Cartão
- **DataTable**: Componente de tabela que exibe registros de compras de cartão com colunas Descrição, Valor Total, Parcelas e Cartão
- **Seeder**: Classe `E2eTestSeeder` do Laravel que popula o banco com dados de teste antes da suíte E2E
- **Modal**: Diálogo modal usado para criação, visualização e edição de compras de cartão
- **FilterBar**: Componente de busca com campo de texto e botões Buscar/Limpar
- **Compra**: Registro de compra no cartão de crédito com campos: cartão (select), descrição, valor total e número de parcelas
- **ModalDialog**: Componente compartilhado (`domain/Shared/components/ui/modal/ModalDialog.vue`) que encapsula o Dialog do reka-ui, usado por todos os módulos para exibir formulários em modal
- **Store_Modal**: Estado reativo da Pinia store que controla a abertura/fechamento do modal via `isModalOpen`, sincronizado com o `ModalDialog` por um `watch`

## Requisitos

### Requisito 0: Correção do Bug de Reabertura do ModalDialog

**User Story:** Como usuário, quero que o modal reabra corretamente após ser fechado pelo botão X, tecla ESC ou clique no overlay, para que eu possa criar/editar/visualizar registros múltiplas vezes sem recarregar a página.

**Contexto do Bug:** Quando o reka-ui fecha o Dialog internamente (via `v-model:open`), o `showDialog` do `ModalDialog` muda para `false`, mas a store do módulo pai mantém `isModalOpen = true`. Na próxima tentativa de abrir o modal, a store tenta setar `isModalOpen = true` (que já é `true`), o `watch` não dispara, e o modal não reabre.

#### Critérios de Aceitação

1. WHEN o reka-ui fechar o Dialog internamente (botão X, ESC ou clique no overlay), THE ModalDialog SHALL emitir um evento `update:open` com o valor `false` para notificar o componente pai
2. WHEN o componente pai receber o evento `update:open` com valor `false` do ModalDialog, THE Store_Modal SHALL atualizar `isModalOpen` para `false` via chamada a `closeModal()`
3. WHEN o usuário clicar no botão "Criar" após ter fechado o modal anteriormente, THE Store_Modal SHALL disparar o `watch` corretamente porque `isModalOpen` transiciona de `false` para `true`
4. THE ModalDialog SHALL manter compatibilidade retroativa com todos os módulos que o utilizam (CreditCard, CreditCardCharge e demais)
5. THE correção SHALL ser aplicada no módulo CreditCard (`Index.vue`) como implementação de referência, servindo de padrão para o CreditCardCharge e demais módulos

### Requisito 1: Page Object para CreditCardCharge

**User Story:** Como desenvolvedor, quero um Page Object dedicado para CreditCardCharge, para que os testes E2E sejam reutilizáveis e fáceis de manter.

#### Critérios de Aceitação

1. THE Page_Object SHALL ser criado no arquivo `e2e/pages/CreditCardChargePage.ts` seguindo a mesma estrutura do `CreditCardPage.ts`
2. THE Page_Object SHALL encapsular navegação para a URL `/finance/credit-card-charges`
3. THE Page_Object SHALL encapsular interações com a DataTable (obter linhas, buscar por descrição, verificar estado vazio)
4. THE Page_Object SHALL encapsular interações com a FilterBar (buscar, limpar busca)
5. THE Page_Object SHALL encapsular interações com paginação (próxima, anterior)
6. THE Page_Object SHALL encapsular interações com o Modal (abrir criação, preencher formulário, submeter, cancelar)
7. THE Page_Object SHALL encapsular interações de visualização (abrir modal de detalhes, verificar campos desabilitados)
8. THE Page_Object SHALL encapsular interações de edição (abrir modal de edição, modificar campos, submeter)
9. THE Page_Object SHALL encapsular interações de exclusão (clicar botão excluir, confirmar exclusão)
10. THE Page_Object SHALL usar `locator('[name="field"]')` para inputs de formulário, `getByRole()` para botões e dialogs, e `getByText()` para conteúdo textual
11. THE Page_Object SHALL definir uma interface `CreditCardChargeFormData` com os campos: `credit_card_uid` (string), `description` (string), `amount` (number) e `total_installments` (number)

### Requisito 2: Dados de Teste no Seeder

**User Story:** Como desenvolvedor, quero dados de teste específicos para CreditCardCharge no seeder, para que os testes E2E tenham dados previsíveis e idempotentes.

#### Critérios de Aceitação

1. THE Seeder SHALL limpar todas as compras de cartão do usuário de teste antes de re-seed (idempotência)
2. THE Seeder SHALL criar pelo menos 3 compras nomeadas com dados previsíveis (descrição, valor, parcelas e cartão conhecidos)
3. THE Seeder SHALL criar compras suficientes via factory para testar paginação (total de registros deve exceder o limite por página de 15)
4. THE Seeder SHALL associar as compras nomeadas aos cartões de crédito nomeados já existentes no seeder (Nubank, Inter, C6 Bank)
5. WHEN o seeder for executado, THE Seeder SHALL também limpar parcelas (installments) associadas às compras removidas para evitar violação de chave estrangeira

### Requisito 3: Testes de Listagem

**User Story:** Como desenvolvedor, quero testes que verifiquem a listagem de compras de cartão, para garantir que a página renderiza corretamente com dados do seeder.

#### Critérios de Aceitação

1. WHEN a página de compras de cartão for acessada, THE Sistema_E2E SHALL verificar que o título "Compras no Cartão" é exibido
2. WHEN a página de compras de cartão for acessada, THE Sistema_E2E SHALL verificar que os registros semeados aparecem na DataTable
3. WHEN uma compra é exibida na DataTable, THE Sistema_E2E SHALL verificar que a linha contém descrição, valor total formatado, número de parcelas e nome do cartão

### Requisito 4: Testes de Busca e Filtro

**User Story:** Como desenvolvedor, quero testes que verifiquem a funcionalidade de busca, para garantir que o filtro por descrição funciona corretamente.

#### Critérios de Aceitação

1. WHEN um termo de busca correspondente a uma descrição existente for digitado, THE Sistema_E2E SHALL verificar que a DataTable exibe apenas compras correspondentes
2. WHEN a busca for limpa, THE Sistema_E2E SHALL verificar que todos os registros voltam a ser exibidos
3. WHEN um termo de busca sem correspondência for digitado, THE Sistema_E2E SHALL verificar que a mensagem "Nenhum registro encontrado." é exibida

### Requisito 5: Testes de Paginação

**User Story:** Como desenvolvedor, quero testes que verifiquem a paginação, para garantir que a navegação entre páginas funciona corretamente.

#### Critérios de Aceitação

1. WHEN o total de registros exceder o limite por página, THE Sistema_E2E SHALL verificar que os controles de paginação (Anterior/Próxima) são visíveis
2. WHEN o botão "Próxima" for clicado, THE Sistema_E2E SHALL verificar que a próxima página de registros é exibida
3. WHEN o botão "Anterior" for clicado na segunda página, THE Sistema_E2E SHALL verificar que a página anterior é exibida
4. WHILE na primeira página, THE Sistema_E2E SHALL verificar que o botão "Anterior" está desabilitado
5. WHILE na última página, THE Sistema_E2E SHALL verificar que o botão "Próxima" está desabilitado

### Requisito 6: Testes de Criação

**User Story:** Como desenvolvedor, quero testes que verifiquem a criação de compras de cartão, para garantir que o fluxo completo de criação funciona.

#### Critérios de Aceitação

1. WHEN o botão "Criar" for clicado, THE Sistema_E2E SHALL verificar que o modal abre com o título "Nova Compra"
2. WHEN todos os campos forem preenchidos corretamente e o formulário for submetido, THE Sistema_E2E SHALL verificar que um toast de sucesso é exibido
3. WHEN uma compra for criada com sucesso, THE Sistema_E2E SHALL verificar que o novo registro aparece na DataTable
4. WHEN o formulário for submetido com dados inválidos (campos obrigatórios vazios), THE Sistema_E2E SHALL verificar que erros de validação são exibidos
5. WHEN o botão "Cancelar" for clicado no modal de criação, THE Sistema_E2E SHALL verificar que o modal fecha sem criar registro

### Requisito 7: Testes de Visualização

**User Story:** Como desenvolvedor, quero testes que verifiquem a visualização de detalhes de uma compra, para garantir que o modo somente-leitura funciona corretamente.

#### Critérios de Aceitação

1. WHEN o ícone de visualização for clicado em uma linha da DataTable, THE Sistema_E2E SHALL verificar que o modal abre com o título "Detalhes da Compra"
2. WHEN o modal de visualização estiver aberto, THE Sistema_E2E SHALL verificar que todos os campos do formulário estão desabilitados (somente-leitura)
3. WHEN o modal de visualização estiver aberto, THE Sistema_E2E SHALL verificar que o botão de submissão (Criar/Salvar) não está visível

### Requisito 8: Testes de Edição

**User Story:** Como desenvolvedor, quero testes que verifiquem a edição de compras de cartão, para garantir que o fluxo de atualização funciona.

**Pré-requisito:** Este requisito depende da implementação do botão de edição na UI (atualmente ausente no `Index.vue`).

#### Critérios de Aceitação

1. WHEN o ícone de edição for clicado em uma linha da DataTable, THE Sistema_E2E SHALL verificar que o modal abre com o título "Editar Compra"
2. WHEN o modal de edição estiver aberto, THE Sistema_E2E SHALL verificar que os campos estão pré-preenchidos com os dados existentes
3. WHEN os campos forem modificados e o formulário for submetido, THE Sistema_E2E SHALL verificar que um toast de sucesso é exibido
4. WHEN uma compra for editada com sucesso, THE Sistema_E2E SHALL verificar que a DataTable reflete os dados atualizados

### Requisito 9: Testes de Exclusão

**User Story:** Como desenvolvedor, quero testes que verifiquem a exclusão de compras de cartão, para garantir que o fluxo de remoção funciona.

**Pré-requisito:** Este requisito depende da implementação do botão de exclusão na UI (atualmente ausente no `Index.vue`).

#### Critérios de Aceitação

1. WHEN o ícone de exclusão for clicado em uma linha da DataTable, THE Sistema_E2E SHALL verificar que um popover de confirmação "Tem certeza?" é exibido
2. WHEN a exclusão for confirmada, THE Sistema_E2E SHALL verificar que um toast de sucesso é exibido
3. WHEN uma compra for excluída com sucesso, THE Sistema_E2E SHALL verificar que o registro é removido da DataTable

### Requisito 10: Organização dos Testes

**User Story:** Como desenvolvedor, quero que os testes sigam as convenções E2E do projeto, para manter consistência com o módulo CreditCard.

#### Critérios de Aceitação

1. THE Sistema_E2E SHALL organizar os testes em blocos `test.describe` separados: Listing, Search, Pagination, Creation, Viewing, Editing, Deletion
2. THE Sistema_E2E SHALL executar testes read-only (Listing, Search, Pagination, Viewing) antes de testes de mutação (Creation, Editing, Deletion)
3. THE Sistema_E2E SHALL criar o arquivo de spec em `e2e/tests/credit-card-charge.spec.ts`
4. THE Sistema_E2E SHALL usar timeout de teste de 15 segundos e timeout de ação de 5 segundos
5. THE Sistema_E2E SHALL aguardar respostas HTTP após ações de navegação (busca, paginação) usando `waitForResponse` em vez de `waitForLoadState('networkidle')`