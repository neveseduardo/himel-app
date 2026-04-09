# Documento de Requisitos — Gestão de Transações por Período

## Introdução

Este documento define os requisitos para melhorias na tela de gestão de períodos e suas transações no Himel App. As melhorias incluem: reordenação do link "Períodos" na sidebar, exibição de dados legíveis (nomes de conta, categoria e descrição) na página de detalhes do período, agrupamento de transações por tipo (entradas/saídas), remoção em lote de transações de um período e criação de novas transações vinculadas ao período via dialog.

## Glossário

- **Sidebar**: Componente de navegação lateral (`AppSidebar.vue`) que lista os links do módulo financeiro.
- **Período**: Registro na tabela `financial_periods` que agrupa transações por mês/ano.
- **Transação**: Registro na tabela `financial_transactions` representando uma movimentação financeira.
- **Conta**: Registro na tabela `financial_accounts` representando uma conta financeira do usuário.
- **Categoria**: Registro na tabela `financial_categories` representando uma categoria financeira.
- **PeriodPageController**: Controller Inertia responsável pelas páginas de período.
- **PeriodService**: Serviço de domínio que encapsula a lógica de negócio de períodos.
- **TransactionResource**: API Resource que serializa transações para o frontend.
- **ModalDialog**: Componente de dialog reutilizável do projeto (`ModalDialog.vue`).
- **TransactionForm**: Componente de formulário reutilizável para criação/edição de transações.

## Requisitos

### Requisito 1: Reordenação do Link "Períodos" na Sidebar

**User Story:** Como usuário, eu quero que o link "Períodos" apareça logo abaixo de "Visão Geral" na sidebar, para que eu acesse rapidamente o CRUD mais importante sem rolar a lista de navegação.

#### Critérios de Aceitação

1. THE Sidebar SHALL exibir o link "Períodos" como segundo item da lista de navegação, imediatamente após "Visão Geral".
2. THE Sidebar SHALL manter "Visão Geral" como primeiro item e página padrão do módulo financeiro.
3. THE Sidebar SHALL preservar a ordem relativa dos demais itens de navegação após "Períodos".

### Requisito 2: Exibição de Dados Legíveis nas Transações do Período

**User Story:** Como usuário, eu quero ver o nome da conta, o nome da categoria e a descrição de cada transação na página de detalhes do período, para que eu não precise interpretar UUIDs.

#### Critérios de Aceitação

1. WHEN o PeriodPageController carregar as transações de um período, THE PeriodService SHALL incluir os relacionamentos `account` e `category` em cada transação retornada (eager loading).
2. WHEN uma transação for serializada para a página de detalhes do período, THE TransactionResource SHALL retornar o objeto `account` com os campos `uid`, `name` e `type`.
3. WHEN uma transação for serializada para a página de detalhes do período, THE TransactionResource SHALL retornar o objeto `category` com os campos `uid`, `name` e `direction`.
4. WHEN uma transação possuir descrição, THE página Show do período SHALL exibir a descrição na coluna correspondente da tabela.
5. WHEN uma transação não possuir descrição, THE página Show do período SHALL exibir um placeholder "—" na coluna de descrição.

### Requisito 3: Agrupamento de Transações por Tipo (Entradas e Saídas)

**User Story:** Como usuário, eu quero ver as transações do período agrupadas em seções de "Entradas" e "Saídas", para que eu tenha uma visão clara e organizada das movimentações.

#### Critérios de Aceitação

1. THE página Show do período SHALL exibir as transações separadas em duas seções visuais: "Entradas" (INFLOW) e "Saídas" (OUTFLOW).
2. WHEN não existirem transações de um determinado tipo no período, THE página Show do período SHALL exibir uma mensagem informativa na seção correspondente indicando a ausência de registros.
3. THE página Show do período SHALL exibir um subtotal para cada seção (Entradas e Saídas) baseado nas transações listadas.

### Requisito 4: Remoção de Todas as Transações de um Período

**User Story:** Como usuário, eu quero poder remover todas as transações de um período de uma só vez, para que eu possa reprocessar o período do zero.

#### Critérios de Aceitação

1. THE página Show do período SHALL exibir um botão "Remover Todas as Transações" na área de ações do cabeçalho.
2. WHEN o usuário clicar no botão de remoção, THE página Show do período SHALL exibir um diálogo de confirmação antes de executar a ação.
3. WHEN o usuário confirmar a remoção, THE PeriodService SHALL desvincular todas as transações do período, definindo `period_uid` como `null` em cada transação.
4. WHEN o usuário confirmar a remoção, THE PeriodService SHALL executar a operação dentro de uma transação de banco de dados para garantir atomicidade.
5. IF a operação de remoção falhar, THEN THE PeriodPageController SHALL retornar uma mensagem de erro e manter as transações inalteradas.
6. WHEN a remoção for concluída com sucesso, THE página Show do período SHALL exibir uma notificação de sucesso e atualizar a listagem de transações.

### Requisito 5: Criação de Nova Transação Vinculada ao Período

**User Story:** Como usuário, eu quero criar uma nova transação diretamente na página de detalhes do período, para que a transação já fique vinculada ao período que estou visualizando.

#### Critérios de Aceitação

1. THE página Show do período SHALL exibir um botão "Nova Transação" na área de ações do cabeçalho.
2. WHEN o usuário clicar no botão "Nova Transação", THE página Show do período SHALL abrir um ModalDialog contendo o formulário de transação.
3. WHEN o formulário de transação for exibido no contexto do período, THE formulário SHALL pré-preencher o campo `period_uid` com o UID do período atual e o campo `occurred_at` com o primeiro dia do mês/ano do período.
4. THE PeriodPageController SHALL fornecer as listas de contas e categorias do usuário como props para a página Show, permitindo que o formulário de transação as utilize.
5. WHEN o usuário submeter o formulário com dados válidos, THE PeriodPageController SHALL criar a transação vinculada ao período e redirecionar de volta à página Show do período.
6. IF a criação da transação falhar na validação, THEN THE formulário SHALL exibir as mensagens de erro nos campos correspondentes.
7. WHEN a transação for criada com sucesso, THE página Show do período SHALL exibir uma notificação de sucesso e atualizar a listagem de transações.
8. THE StoreTransactionRequest SHALL aceitar o campo opcional `period_uid` para vincular a transação ao período.
