# Documento de Requisitos — Sistema CRUD Frontend Modular

## Introdução

Este documento especifica os requisitos para a refatoração do frontend do Himel App, transformando as páginas CRUD atuais (Create/Edit em páginas separadas com `confirm()` nativo para exclusão) em um sistema baseado em modais reutilizáveis, stores Pinia por módulo, validação vee-validate + zod, confirmação de exclusão via popover (estilo shadcn), e rotas Wayfinder tipadas. O objetivo é centralizar operações CRUD na página Index de cada módulo financeiro, eliminando navegação desnecessária entre páginas.

## Glossário

- **Sistema_CRUD**: Conjunto de componentes Vue 3, stores Pinia e validações que implementam operações Create, Read, Update e Delete para módulos financeiros.
- **PageHeader**: Componente Vue reutilizável que exibe título da página e botão de ação primária (ex: "Criar").
- **ModalDialog**: Componente Vue genérico baseado no Dialog do shadcn/vue que renderiza conteúdo dinâmico via slots, com props `title` e `subtitle`.
- **DeleteConfirmPopover**: Componente Vue baseado no Popover do shadcn/vue que exibe confirmação de exclusão inline na linha da tabela.
- **Module_Store**: Store Pinia dedicado a um módulo financeiro, responsável por gerenciar estado do modal (abertura, modo, item atual) e estado de exclusão.
- **Module_Form**: Componente Vue de formulário específico de um módulo financeiro, reutilizável para criação, edição e visualização.
- **ValidatedInertiaForm**: Componente Vue existente que integra vee-validate + zod com o router do Inertia.js para submissão de formulários com validação dupla.
- **ValidatedField**: Componente Vue existente que renderiza campos de formulário com exibição de erros inline via vee-validate.
- **Wayfinder**: Gerador de rotas tipadas do Laravel que produz funções TypeScript importáveis de `@/actions/`.
- **DataTable**: Componente Vue existente que renderiza tabelas com colunas dinâmicas e slots para células customizadas.
- **Zod_Schema**: Schema de validação definido com a biblioteca zod, usado para validação frontend de formulários.
- **Soft_Delete**: Exclusão lógica de registros no backend Laravel, sem remoção física do banco de dados.
- **Index_Page**: Página Vue principal de cada módulo financeiro que centraliza listagem, filtros, paginação e operações CRUD via modais.

## Requisitos

### Requisito 1: Componente PageHeader Reutilizável

**User Story:** Como desenvolvedor, quero um componente de cabeçalho reutilizável para páginas Index, para que todos os módulos financeiros tenham um layout consistente com título e botão de ação primária.

#### Critérios de Aceitação

1.1. THE PageHeader SHALL renderizar o título da página recebido via prop `title` com estilo consistente.
1.2. THE PageHeader SHALL renderizar um botão de ação primária com label recebido via prop `buttonLabel`.
1.3. WHERE a prop `buttonIcon` for fornecida, THE PageHeader SHALL renderizar o ícone dentro do botão de ação.
1.4. WHEN o usuário clicar no botão de ação primária, THE PageHeader SHALL emitir o evento `action`.
1.5. THE PageHeader SHALL manter layout flex com `justify-between` para alinhar título à esquerda e botão à direita.

---

### Requisito 2: Componente ModalDialog com Conteúdo Dinâmico

**User Story:** Como desenvolvedor, quero um componente modal genérico com suporte a título, subtítulo e conteúdo dinâmico via slots, para que eu possa reutilizá-lo em operações de criação, edição e visualização de qualquer módulo.

#### Critérios de Aceitação

2.1. THE ModalDialog SHALL renderizar um Dialog do shadcn/vue com título recebido via prop `title`.
2.2. WHERE a prop `subtitle` for fornecida, THE ModalDialog SHALL renderizar o subtítulo abaixo do título.
2.3. THE ModalDialog SHALL aceitar conteúdo dinâmico via slot default para renderização dentro do corpo do dialog.
2.4. THE ModalDialog SHALL expor os métodos `openDialog()` e `closeDialog()` via `defineExpose` para controle externo.
2.5. WHEN o método `openDialog()` for chamado, THE ModalDialog SHALL exibir o dialog.
2.6. WHEN o método `closeDialog()` for chamado, THE ModalDialog SHALL fechar o dialog.

---

### Requisito 3: Componente DeleteConfirmPopover

**User Story:** Como usuário, quero confirmar a exclusão de registros via um popover inline na tabela, para que eu tenha uma experiência de confirmação mais elegante do que o `confirm()` nativo do browser.

#### Critérios de Aceitação

3.1. THE DeleteConfirmPopover SHALL renderizar um Popover do shadcn/vue com mensagem de confirmação.
3.2. THE DeleteConfirmPopover SHALL exibir botões "Cancelar" e "Excluir" dentro do popover.
3.3. WHEN o usuário clicar no botão "Excluir" do popover, THE DeleteConfirmPopover SHALL emitir o evento `confirm`.
3.4. WHEN o usuário clicar no botão "Cancelar" do popover, THE DeleteConfirmPopover SHALL emitir o evento `cancel`.
3.5. THE DeleteConfirmPopover SHALL aceitar slot `trigger` para renderizar o botão que abre o popover.
3.6. WHILE a prop `loading` for `true`, THE DeleteConfirmPopover SHALL exibir estado de carregamento no botão "Excluir".
3.7. THE Sistema_CRUD SHALL utilizar o DeleteConfirmPopover para todas as operações de exclusão, substituindo o `confirm()` nativo do browser.

---

### Requisito 4: Stores Pinia por Módulo Financeiro

**User Story:** Como desenvolvedor, quero stores Pinia dedicados para cada módulo financeiro, para que o estado de UI (modal, item atual, modo) seja gerenciado de forma centralizada e reativa.

#### Critérios de Aceitação

4.1. THE Sistema_CRUD SHALL criar um Module_Store Pinia para cada módulo financeiro: accounts, categories, transactions, transfers, fixed-expenses, credit-cards e credit-card-charges.
4.2. THE Module_Store SHALL manter estado reativo para: `isModalOpen` (boolean), `modalMode` ('create' | 'edit' | 'view'), `currentItem` (item do módulo ou null) e `deletingUid` (string ou null).
4.3. WHEN a ação `openCreateModal()` for chamada, THE Module_Store SHALL definir `currentItem` como `null`, `modalMode` como `'create'` e `isModalOpen` como `true`.
4.4. WHEN a ação `openEditModal(item)` for chamada, THE Module_Store SHALL definir `currentItem` com o item recebido, `modalMode` como `'edit'` e `isModalOpen` como `true`.
4.5. WHEN a ação `openViewModal(item)` for chamada, THE Module_Store SHALL definir `currentItem` com o item recebido, `modalMode` como `'view'` e `isModalOpen` como `true`.
4.6. WHEN a ação `closeModal()` for chamada, THE Module_Store SHALL definir `isModalOpen` como `false` e, após delay de 200ms para animação, redefinir `currentItem` como `null` e `modalMode` como `'create'`.
4.7. THE Module_Store SHALL ser singleton por módulo, retornando a mesma instância em chamadas subsequentes.

---

### Requisito 5: Formulários Reutilizáveis para Criação e Edição

**User Story:** Como desenvolvedor, quero que cada módulo tenha um único componente de formulário reutilizável para criação, edição e visualização, para que eu evite duplicação de código entre operações CRUD.

#### Critérios de Aceitação

5.1. THE Module_Form SHALL aceitar prop opcional `item` para diferenciar entre modo criação (ausente) e modo edição (presente).
5.2. WHERE a prop `readonly` for `true`, THE Module_Form SHALL renderizar todos os campos como desabilitados para modo visualização.
5.3. WHEN o Module_Form estiver em modo criação, THE Module_Form SHALL enviar requisição POST via Inertia router para a rota Wayfinder `store`.
5.4. WHEN o Module_Form estiver em modo edição, THE Module_Form SHALL enviar requisição PUT via Inertia router para a rota Wayfinder `update` com o uid do item.
5.5. WHEN a submissão for bem-sucedida, THE Module_Form SHALL emitir o evento `success`.
5.6. THE Module_Form SHALL utilizar o ValidatedInertiaForm com Zod_Schema para validação de campos.

---

### Requisito 6: Validação de Formulários com vee-validate e zod

**User Story:** Como usuário, quero que os formulários validem meus dados em tempo real antes de enviar ao servidor, para que eu receba feedback imediato sobre erros de preenchimento.

#### Critérios de Aceitação

6.1. WHEN o usuário digitar em um campo do formulário, THE ValidatedField SHALL validar o valor contra o Zod_Schema e exibir erro inline caso inválido.
6.2. WHEN o usuário submeter o formulário, THE ValidatedInertiaForm SHALL executar validação completa do Zod_Schema antes de enviar a requisição HTTP.
6.3. IF a validação frontend do Zod_Schema falhar, THEN THE ValidatedInertiaForm SHALL exibir erros inline nos campos e impedir o envio da requisição HTTP.
6.4. IF o backend retornar erro 422 com erros de validação, THEN THE ValidatedInertiaForm SHALL mapear os erros do backend para os campos correspondentes via `setErrors` do vee-validate.
6.5. THE Sistema_CRUD SHALL manter Zod_Schemas de validação para cada módulo financeiro em `modules/finance/validations/`.

---

### Requisito 7: Página Index como Ponto Central do CRUD

**User Story:** Como usuário, quero realizar todas as operações CRUD (criar, visualizar, editar, excluir) a partir da página de listagem de cada módulo, para que eu não precise navegar entre páginas diferentes.

#### Critérios de Aceitação

7.1. THE Index_Page SHALL exibir a listagem de registros do módulo via DataTable com colunas dinâmicas.
7.2. WHEN o usuário clicar no botão "Criar" do PageHeader, THE Index_Page SHALL abrir o ModalDialog em modo criação com o Module_Form vazio.
7.3. WHEN o usuário clicar na ação "Ver" de uma linha da tabela, THE Index_Page SHALL abrir o ModalDialog em modo visualização com os dados do registro.
7.4. WHEN o usuário clicar na ação "Editar" de uma linha da tabela, THE Index_Page SHALL abrir o ModalDialog em modo edição com os dados do registro preenchidos no Module_Form.
7.5. WHEN o usuário clicar na ação "Excluir" de uma linha da tabela, THE Index_Page SHALL exibir o DeleteConfirmPopover para confirmação.
7.6. WHEN a operação CRUD for concluída com sucesso, THE Index_Page SHALL recarregar os dados da tabela via Inertia e exibir toast de sucesso via vue-sonner.
7.7. IF uma operação CRUD falhar, THEN THE Index_Page SHALL exibir toast de erro via vue-sonner com a mensagem retornada pelo backend.

---

### Requisito 8: Rotas Wayfinder Tipadas para Todas as Ações CRUD

**User Story:** Como desenvolvedor, quero que todas as URLs de ações CRUD sejam geradas via Wayfinder, para que eu tenha tipagem estrita e elimine URLs hardcoded em strings.

#### Critérios de Aceitação

8.1. THE Sistema_CRUD SHALL importar funções de rota de `@/actions/` geradas pelo Wayfinder para todas as ações CRUD (index, store, update, destroy).
8.2. THE Sistema_CRUD SHALL utilizar o método `.url()` das funções Wayfinder para gerar URLs dinâmicas com parâmetros (ex: `destroy.url(uid)`).
8.3. THE Sistema_CRUD SHALL proibir o uso de URLs em string pura para qualquer operação CRUD.

---

### Requisito 9: Exclusão com Soft Delete

**User Story:** Como usuário, quero que a exclusão de registros seja lógica (soft delete), para que meus dados possam ser recuperados caso necessário.

#### Critérios de Aceitação

9.1. WHEN o usuário confirmar a exclusão via DeleteConfirmPopover, THE Sistema_CRUD SHALL enviar requisição DELETE via Inertia router para a rota Wayfinder `destroy`.
9.2. WHILE a requisição de exclusão estiver em andamento, THE DeleteConfirmPopover SHALL exibir estado de loading e THE Module_Store SHALL manter o `deletingUid` com o uid do item.
9.3. WHEN a exclusão for concluída (sucesso ou erro), THE Module_Store SHALL redefinir `deletingUid` como `null`.

---

### Requisito 10: Feedback ao Usuário via Toasts

**User Story:** Como usuário, quero receber notificações visuais sobre o resultado de minhas ações, para que eu saiba se a operação foi bem-sucedida ou se houve erro.

#### Critérios de Aceitação

10.1. WHEN uma operação de criação for bem-sucedida, THE Sistema_CRUD SHALL exibir toast de sucesso via vue-sonner.
10.2. WHEN uma operação de edição for bem-sucedida, THE Sistema_CRUD SHALL exibir toast de sucesso via vue-sonner.
10.3. WHEN uma operação de exclusão for bem-sucedida, THE Sistema_CRUD SHALL exibir toast de sucesso via vue-sonner.
10.4. IF uma operação CRUD retornar erro do backend, THEN THE Sistema_CRUD SHALL exibir toast de erro via vue-sonner com a mensagem de erro retornada.

---

### Requisito 11: Remoção de Páginas Create e Edit Separadas

**User Story:** Como desenvolvedor, quero remover as páginas Create.vue e Edit.vue separadas de cada módulo, para que o CRUD seja centralizado na Index_Page via modais e o código duplicado seja eliminado.

#### Critérios de Aceitação

11.1. WHEN a migração para modais estiver completa para um módulo, THE Sistema_CRUD SHALL remover os arquivos `Create.vue` e `Edit.vue` do diretório de páginas desse módulo.
11.2. WHEN as páginas Create e Edit forem removidas, THE Sistema_CRUD SHALL remover os métodos `create()` e `edit()` correspondentes nos PageControllers do backend que renderizavam páginas Inertia separadas.
11.3. THE Index_Page SHALL substituir completamente a funcionalidade das páginas Create.vue e Edit.vue removidas.

---

### Requisito 12: Tipagem Estrita TypeScript

**User Story:** Como desenvolvedor, quero que todo o código Vue utilize TypeScript estrito, para que erros de tipo sejam detectados em tempo de compilação.

#### Critérios de Aceitação

12.1. THE Sistema_CRUD SHALL utilizar `<script setup lang="ts">` em todos os componentes Vue.
12.2. THE Sistema_CRUD SHALL proibir o uso do tipo `any` em todo o código TypeScript.
12.3. THE Sistema_CRUD SHALL reutilizar os tipos existentes definidos em `modules/finance/types/finance.ts` para todos os módulos.

---

### Requisito 13: Tratamento de Erros

**User Story:** Como usuário, quero que o sistema trate erros de forma elegante, para que eu saiba o que aconteceu e possa tentar novamente.

#### Critérios de Aceitação

13.1. IF a validação frontend do Zod_Schema falhar, THEN THE ValidatedInertiaForm SHALL exibir erros inline nos campos correspondentes sem enviar requisição HTTP.
13.2. IF o backend retornar erro 422, THEN THE ValidatedInertiaForm SHALL mapear erros do backend para campos do formulário e exibi-los inline.
13.3. IF o backend retornar erro 500, THEN THE Sistema_CRUD SHALL exibir toast de erro genérico via vue-sonner.
13.4. IF a exclusão falhar por dependências (ex: conta com transações), THEN THE Sistema_CRUD SHALL exibir toast de erro com mensagem explicativa e redefinir `deletingUid` como `null`.
13.5. IF ocorrer erro de rede, THEN THE Sistema_CRUD SHALL exibir toast de erro e manter o modal aberto com dados preenchidos para nova tentativa.
