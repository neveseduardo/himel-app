# Tasks: Correção de Dessincronização do Dialog do CreditCard

## Task 1: Emitir evento `update:open` no ModalDialog.vue
- [x] 1.1 Adicionar `defineEmits` com evento `update:open` em `ModalDialog.vue`
- [x] 1.2 Adicionar `watch` no `showDialog` que emite `update:open` quando o valor muda

### Arquivos:
- `resources/js/domain/Shared/components/ui/modal/ModalDialog.vue`

### Requisitos atendidos: 2.1

---

## Task 2: Sincronizar store no Index.vue do CreditCard
- [x] 2.1 Adicionar handler `@update:open` no `<ModalDialog>` em `Index.vue` que chama `store.closeModal()` quando `open` é `false`

### Arquivos:
- `resources/js/pages/finance/credit-cards/Index.vue`

### Requisitos atendidos: 2.1, 2.2, 2.3

---

## Task 3: Teste E2E — Dialog reabre após fechamento via ESC/overlay
- [x] 3.1 Adicionar métodos `closeDialogByEsc()` e `closeDialogByOverlay()` no Page Object `CreditCardPage.ts`
- [x] 3.2 Adicionar `test.describe('CreditCard Dialog Reopen')` em `credit-card.spec.ts` com testes para: abrir → fechar(ESC) → abrir novamente; abrir → fechar(overlay) → abrir novamente
- [x] 3.3 Executar suite E2E completa para garantir preservation checking (testes existentes continuam passando)

### Arquivos:
- `e2e/pages/CreditCardPage.ts`
- `e2e/tests/credit-card.spec.ts`

### Requisitos atendidos: 2.4, 3.1, 3.2, 3.3, 3.4
