# Plano de Implementação: Sistema CRUD Frontend Modular

## Visão Geral

Refatoração incremental do frontend do Himel App para centralizar operações CRUD na página Index de cada módulo financeiro via modais, stores Pinia, validação vee-validate + zod, confirmação de exclusão via popover, e rotas Wayfinder tipadas. A implementação segue a ordem: componentes compartilhados → stores → formulários → páginas Index → limpeza.

## Tasks

- [x] 1. Criar componentes compartilhados (PageHeader e DeleteConfirmPopover)
  - [x] 1.1 Criar componente `PageHeader.vue` em `resources/js/components/`
    - Implementar props `title`, `buttonLabel`, `buttonIcon` (opcional)
    - Emitir evento `action` ao clicar no botão
    - Layout flex com `justify-between`, título `text-2xl font-semibold`
    - Usar `Button` do shadcn/vue com ícone Lucide opcional
    - _Requisitos: 1.1, 1.2, 1.3, 1.4, 1.5_

  - [x] 1.2 Criar componente `DeleteConfirmPopover.vue` em `resources/js/components/`
    - Usar `Popover`, `PopoverTrigger`, `PopoverContent` do shadcn/vue
    - Props: `title` (default "Tem certeza?"), `description` (default "Esta ação não pode ser desfeita."), `loading`
    - Emitir eventos `confirm` e `cancel`
    - Slot `trigger` para o botão que abre o popover
    - Estado de loading no botão "Excluir" quando `loading === true`
    - _Requisitos: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7_

  - [x] 1.3 Aprimorar `ModalDialog.vue` existente em `resources/js/components/ui/modal/`
    - Renomear prop `description` para `subtitle` (ou adicionar alias) conforme design
    - Garantir que `openDialog()` e `closeDialog()` estão expostos via `defineExpose`
    - Verificar que o slot default funciona para conteúdo dinâmico
    - _Requisitos: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6_

  - [ ]* 1.4 Escrever testes unitários para PageHeader e DeleteConfirmPopover
    - Testar renderização de props (title, buttonLabel, buttonIcon)
    - Testar emissão de eventos (action, confirm, cancel)
    - Testar estado de loading do DeleteConfirmPopover
    - _Requisitos: 1.1–1.5, 3.1–3.7_

- [x] 2. Checkpoint — Verificar componentes compartilhados
  - Garantir que todos os componentes compartilhados estão funcionando. Perguntar ao usuário se há dúvidas.

- [x] 3. Criar stores Pinia por módulo financeiro
  - [x] 3.1 Criar `useAccountStore.ts` em `resources/js/modules/finance/stores/`
    - Estado reativo: `isModalOpen`, `modalMode` ('create' | 'edit' | 'view'), `currentItem`, `deletingUid`
    - Ações: `openCreateModal()`, `openEditModal(item)`, `openViewModal(item)`, `closeModal()`
    - `closeModal()` com delay de 200ms para reset de `currentItem` e `modalMode`
    - Usar `defineStore` com Composition API (setup stores)
    - _Requisitos: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7_

  - [x] 3.2 Criar stores restantes seguindo o mesmo padrão do `useAccountStore`
    - `useCategoryStore.ts` — tipo `Category`
    - `useTransactionStore.ts` — tipo `Transaction`
    - `useTransferStore.ts` — tipo `Transfer`
    - `useFixedExpenseStore.ts` — tipo `FixedExpense`
    - `useCreditCardStore.ts` — tipo `CreditCard`
    - `useCreditCardChargeStore.ts` — tipo `CreditCardCharge`
    - Cada store é singleton por módulo
    - _Requisitos: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7_

  - [ ]* 3.3 Escrever testes para stores Pinia
    - **Property 1: Store modal state transitions** — Verificar que `openCreateModal()` resulta em `isModalOpen === true`, `modalMode === 'create'`, `currentItem === null`; `openEditModal(item)` resulta em `isModalOpen === true`, `modalMode === 'edit'`, `currentItem === item`; `openViewModal(item)` resulta em `isModalOpen === true`, `modalMode === 'view'`, `currentItem === item`
    - **Valida: Requisitos 4.3, 4.4, 4.5**
    - **Property 2: Store closeModal resets state** — Verificar que `closeModal()` define `isModalOpen === false` imediatamente e após 200ms `currentItem === null` e `modalMode === 'create'`
    - **Valida: Requisito 4.6**

- [x] 4. Checkpoint — Verificar stores Pinia
  - Garantir que todos os stores estão criados e tipados corretamente. Perguntar ao usuário se há dúvidas.

- [x] 5. Refatorar formulários de módulo para suportar modal e modo readonly
  - [x] 5.1 Refatorar `AccountForm.vue` em `resources/js/modules/finance/components/`
    - Adicionar props `item?: Account` e `readonly?: boolean`
    - Substituir `useForm` do Inertia por `ValidatedInertiaForm` + `ValidatedField` com schema zod (`accountSchema`)
    - Usar rotas Wayfinder (`store.url()` para POST, `update.url(uid)` para PUT) em vez de URLs hardcoded
    - Emitir evento `success` após submissão bem-sucedida
    - Emitir evento `cancel` ao clicar em Cancelar
    - Remover Card/CardHeader/CardFooter (formulário será renderizado dentro do ModalDialog)
    - Modo readonly: desabilitar todos os campos quando `readonly === true`
    - _Requisitos: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 6.1, 6.2, 6.3, 6.4, 8.1, 8.2, 8.3, 12.1, 12.2_

  - [x] 5.2 Refatorar `CategoryForm.vue` seguindo o mesmo padrão do AccountForm
    - Props `item?: Category`, `readonly?: boolean`
    - Usar `ValidatedInertiaForm` + `ValidatedField` + `categorySchema`
    - Rotas Wayfinder para store/update
    - _Requisitos: 5.1–5.6, 6.1–6.4, 8.1–8.3_

  - [x] 5.3 Refatorar `TransactionForm.vue` seguindo o mesmo padrão
    - Props `item?: Transaction`, `readonly?: boolean`
    - Usar `ValidatedInertiaForm` + `ValidatedField` + `transactionSchema`
    - Rotas Wayfinder para store/update
    - _Requisitos: 5.1–5.6, 6.1–6.4, 8.1–8.3_

  - [x] 5.4 Refatorar `TransferForm.vue` seguindo o mesmo padrão
    - Props `item?: Transfer`, `readonly?: boolean`
    - Usar `ValidatedInertiaForm` + `ValidatedField` + `transferSchema`
    - Rotas Wayfinder para store
    - _Requisitos: 5.1–5.6, 6.1–6.4, 8.1–8.3_

  - [x] 5.5 Refatorar `FixedExpenseForm.vue` seguindo o mesmo padrão
    - Props `item?: FixedExpense`, `readonly?: boolean`
    - Usar `ValidatedInertiaForm` + `ValidatedField` + `fixedExpenseSchema`
    - Rotas Wayfinder para store/update
    - _Requisitos: 5.1–5.6, 6.1–6.4, 8.1–8.3_

  - [x] 5.6 Refatorar `CreditCardForm.vue` seguindo o mesmo padrão
    - Props `item?: CreditCard`, `readonly?: boolean`
    - Usar `ValidatedInertiaForm` + `ValidatedField` + `creditCardSchema`
    - Rotas Wayfinder para store/update
    - _Requisitos: 5.1–5.6, 6.1–6.4, 8.1–8.3_

  - [x] 5.7 Refatorar `CreditCardChargeForm.vue` seguindo o mesmo padrão
    - Props `item?: CreditCardCharge`, `readonly?: boolean`
    - Usar `ValidatedInertiaForm` + `ValidatedField` + `creditCardChargeSchema`
    - Rotas Wayfinder para store
    - _Requisitos: 5.1–5.6, 6.1–6.4, 8.1–8.3_

- [x] 6. Checkpoint — Verificar formulários refatorados
  - Garantir que todos os formulários compilam sem erros de tipo (`npx vue-tsc --noEmit`). Perguntar ao usuário se há dúvidas.

- [x] 7. Refatorar páginas Index para usar modais, stores e Wayfinder
  - [x] 7.1 Refatorar `pages/finance/accounts/Index.vue`
    - Substituir cabeçalho inline por `PageHeader` com evento `action` → `store.openCreateModal()`
    - Integrar `useAccountStore` para controle de modal
    - Adicionar `ModalDialog` com título dinâmico (create/edit/view) e `AccountForm` dentro
    - Watcher em `store.isModalOpen` para abrir/fechar modal via ref
    - Substituir `confirm()` nativo por `DeleteConfirmPopover` nas ações da tabela
    - Adicionar botão "Ver" nas ações da tabela → `store.openViewModal(item)`
    - Substituir todas as URLs hardcoded por rotas Wayfinder (`index`, `destroy` de `@/actions/...`)
    - Implementar `handleDelete()` com `router.delete()` via Wayfinder, toast de sucesso/erro via vue-sonner
    - Remover `Link` para Create/Edit (substituídos por modais)
    - _Requisitos: 7.1–7.7, 8.1–8.3, 9.1–9.3, 10.1–10.4, 13.3–13.5_

  - [x] 7.2 Refatorar `pages/finance/categories/Index.vue` seguindo o mesmo padrão
    - Usar `useCategoryStore`, `CategoryForm`, rotas Wayfinder de Category
    - _Requisitos: 7.1–7.7, 8.1–8.3, 9.1–9.3, 10.1–10.4_

  - [x] 7.3 Refatorar `pages/finance/transactions/Index.vue` seguindo o mesmo padrão
    - Usar `useTransactionStore`, `TransactionForm`, rotas Wayfinder de Transaction
    - _Requisitos: 7.1–7.7, 8.1–8.3, 9.1–9.3, 10.1–10.4_

  - [x] 7.4 Refatorar `pages/finance/transfers/Index.vue` seguindo o mesmo padrão
    - Usar `useTransferStore`, `TransferForm`, rotas Wayfinder de Transfer
    - Nota: transfers não possuem Edit (apenas Create e View)
    - _Requisitos: 7.1–7.7, 8.1–8.3, 9.1–9.3, 10.1–10.4_

  - [x] 7.5 Refatorar `pages/finance/fixed-expenses/Index.vue` seguindo o mesmo padrão
    - Usar `useFixedExpenseStore`, `FixedExpenseForm`, rotas Wayfinder de FixedExpense
    - _Requisitos: 7.1–7.7, 8.1–8.3, 9.1–9.3, 10.1–10.4_

  - [x] 7.6 Refatorar `pages/finance/credit-cards/Index.vue` seguindo o mesmo padrão
    - Usar `useCreditCardStore`, `CreditCardForm`, rotas Wayfinder de CreditCard
    - _Requisitos: 7.1–7.7, 8.1–8.3, 9.1–9.3, 10.1–10.4_

  - [x] 7.7 Refatorar `pages/finance/credit-card-charges/Index.vue` seguindo o mesmo padrão
    - Usar `useCreditCardChargeStore`, `CreditCardChargeForm`, rotas Wayfinder de CreditCardCharge
    - Nota: credit-card-charges possui Show.vue (verificar se será substituído por modal view)
    - _Requisitos: 7.1–7.7, 8.1–8.3, 9.1–9.3, 10.1–10.4_

  - [ ]* 7.8 Escrever testes para o fluxo de exclusão
    - **Property 5: Deletion state cleanup** — Verificar que `deletingUid` retorna a `null` após exclusão (sucesso ou erro)
    - **Valida: Requisito 9.3**

- [x] 8. Checkpoint — Verificar páginas Index refatoradas
  - Executar `npm run lint`, `npx vue-tsc --noEmit` e `npm run build`. Garantir que tudo compila sem erros. Perguntar ao usuário se há dúvidas.

- [x] 9. Remover páginas Create/Edit obsoletas e limpar backend
  - [x] 9.1 Remover arquivos `Create.vue` e `Edit.vue` de todos os módulos
    - `pages/finance/accounts/Create.vue` e `Edit.vue`
    - `pages/finance/categories/Create.vue` e `Edit.vue`
    - `pages/finance/transactions/Create.vue` e `Edit.vue`
    - `pages/finance/transfers/Create.vue` (não possui Edit)
    - `pages/finance/fixed-expenses/Create.vue` e `Edit.vue`
    - `pages/finance/credit-cards/Create.vue` e `Edit.vue`
    - `pages/finance/credit-card-charges/Create.vue` e `Show.vue` (se substituído por modal view)
    - _Requisitos: 11.1, 11.3_

  - [x] 9.2 Remover métodos `create()` e `edit()` dos PageControllers no backend
    - Remover métodos que renderizavam páginas Inertia separadas para Create/Edit
    - Manter métodos `index()`, `store()`, `update()`, `destroy()` intactos
    - Remover rotas correspondentes no backend (GET create, GET edit)
    - Executar `vendor/bin/pint --dirty --format agent` após alterações PHP
    - _Requisitos: 11.2_

  - [ ]* 9.3 Atualizar testes backend existentes
    - Remover ou atualizar testes que referenciam rotas create/edit removidas
    - Garantir que testes de store/update/destroy continuam passando
    - Executar `php artisan test --compact` para validar
    - _Requisitos: 11.2_

- [x] 10. Validação final e build
  - [x] 10.1 Executar verificações de qualidade
    - `npm run lint` — sem erros
    - `npx vue-tsc --noEmit` — sem erros de tipo
    - `npm run build` — build bem-sucedido
    - `php artisan test --compact` — todos os testes passando
    - _Requisitos: 12.1, 12.2, 12.3_

  - [x] 10.2 Regenerar rotas Wayfinder
    - Executar `php artisan wayfinder:generate` para atualizar funções TypeScript após remoção de rotas
    - Verificar que imports de `@/actions/` não referenciam controllers/métodos removidos
    - _Requisitos: 8.1, 8.2, 8.3_

- [x] 11. Checkpoint final — Garantir que tudo funciona
  - Garantir que todos os testes passam, build compila sem erros, e o sistema está funcional. Perguntar ao usuário se há dúvidas.

- [x] 12. Padronizar notificações toast com composable `useCrudToast`
  - [x] 12.1 Criar composable `useCrudToast.ts` em `resources/js/modules/finance/composables/`
    - Exportar função `useCrudToast(entityLabel: string)` que retorna `{ onSuccess, onError }`
    - `onSuccess(operation)` exibe `toast.success` com mensagem padronizada por operação (create/update/delete)
    - `onError(operation, errors?)` exibe `toast.error` com mensagem do backend (se disponível) ou fallback padronizado
    - Mensagens em português: "criado(a) com sucesso!", "atualizado(a) com sucesso!", "excluído(a) com sucesso!"
    - _Requisitos: 10.5, 10.6_

  - [x] 12.2 Refatorar `pages/finance/accounts/Index.vue` para usar `useCrudToast`
    - Importar e inicializar `useCrudToast('Conta')`
    - Substituir `toast.success('Conta excluída com sucesso!')` por `onSuccess('delete')`
    - Substituir `toast.error(...)` no handleDelete por `onError('delete', errors)`
    - Criar `handleFormSuccess()` que chama `onSuccess('create')` ou `onSuccess('update')` baseado em `store.modalMode`, e depois `store.closeModal()`
    - Alterar `@success="store.closeModal()"` para `@success="handleFormSuccess"` no template
    - Remover import direto de `toast` de `vue-sonner`
    - _Requisitos: 10.1, 10.2, 10.3, 10.4, 10.6, 10.7_

  - [x] 12.3 Refatorar `pages/finance/categories/Index.vue` para usar `useCrudToast`
    - Mesmo padrão do 12.2 com `useCrudToast('Categoria')`
    - _Requisitos: 10.1, 10.2, 10.3, 10.4, 10.6, 10.7_

  - [x] 12.4 Refatorar `pages/finance/transactions/Index.vue` para usar `useCrudToast`
    - Mesmo padrão do 12.2 com `useCrudToast('Transação')`
    - _Requisitos: 10.1, 10.2, 10.3, 10.4, 10.6, 10.7_

  - [x] 12.5 Refatorar `pages/finance/transfers/Index.vue` para usar `useCrudToast`
    - Mesmo padrão do 12.2 com `useCrudToast('Transferência')`
    - Nota: transfers não possuem Edit, apenas Create e Delete
    - _Requisitos: 10.1, 10.3, 10.4, 10.6, 10.7_

  - [x] 12.6 Refatorar `pages/finance/fixed-expenses/Index.vue` para usar `useCrudToast`
    - Mesmo padrão do 12.2 com `useCrudToast('Despesa fixa')`
    - _Requisitos: 10.1, 10.2, 10.3, 10.4, 10.6, 10.7_

  - [x] 12.7 Refatorar `pages/finance/credit-cards/Index.vue` para usar `useCrudToast`
    - Mesmo padrão do 12.2 com `useCrudToast('Cartão')`
    - _Requisitos: 10.1, 10.2, 10.3, 10.4, 10.6, 10.7_

  - [x] 12.8 Refatorar `pages/finance/credit-card-charges/Index.vue` para usar `useCrudToast`
    - Mesmo padrão do 12.2 com `useCrudToast('Compra no cartão')`
    - Nota: credit-card-charges possui apenas Create e View (sem Edit/Delete na Index)
    - _Requisitos: 10.1, 10.4, 10.6, 10.7_

- [x] 13. Validação final — Toast padronizado
  - [x] 13.1 Executar verificações de qualidade
    - `npm run lint` — sem erros
    - `npx vue-tsc --noEmit` — sem erros de tipo
    - `npm run build` — build bem-sucedido
    - Verificar que nenhuma página Index importa `toast` diretamente de `vue-sonner` (exceto o composable)
    - _Requisitos: 10.5, 10.6, 12.1, 12.2_

## Notas

- Tasks marcadas com `*` são opcionais e podem ser puladas para um MVP mais rápido
- Cada task referencia requisitos específicos para rastreabilidade
- Checkpoints garantem validação incremental
- A ordem de implementação garante que dependências são resolvidas antes do uso (componentes → stores → forms → pages → cleanup → toast)
- Commits devem ser feitos após cada grupo de tasks (1, 3, 5, 7, 9, 12) para versionamento granular
- Tasks 12.x padronizam toasts que antes eram hardcoded em cada página Index, centralizando no composable `useCrudToast`
