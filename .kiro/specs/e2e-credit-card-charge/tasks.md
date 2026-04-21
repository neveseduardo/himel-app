# Tarefas de Implementação — E2E CreditCardCharge (Incremento)

## Tarefa 4: Dialog Sync Fix — CreditCardCharge Index.vue
- [x] 4.1 Adicionar handler `handleDialogOpenChange(open: boolean)` que chama `store.closeModal()` quando `open` é `false` e `store.isModalOpen` é `true`
- [x] 4.2 Adicionar `@update:open="handleDialogOpenChange"` ao `<ModalDialog>` no template

## Tarefa 5: Dialog Sync Fix — Page Object e Testes E2E
- [x] 5.1 Adicionar métodos `closeDialogByEsc()` e `closeDialogByOverlay()` ao `CreditCardChargePage.ts` (seguindo padrão do `CreditCardPage.ts`)
- [x] 5.2 Adicionar bloco `CreditCardCharge Dialog Reopen` ao `credit-card-charge.spec.ts` com 2 testes: reabertura via ESC e via overlay

## Tarefa 6: Campo purchase_date — Backend
- [x] 6.1 Criar migration para adicionar coluna `purchase_date` (date, nullable) à tabela `financial_credit_card_charges`
- [x] 6.2 Rodar a migration
- [x] 6.3 Atualizar Model `CreditCardCharge.php`: adicionar `purchase_date` ao `$fillable` e `$casts`
- [x] 6.4 Atualizar `CreditCardChargeResource.php`: incluir `purchase_date` formatado como `Y-m-d`
- [x] 6.5 Atualizar `StoreCreditCardChargeRequest.php`: adicionar validação `purchase_date` (required, date) com mensagens
- [x] 6.6 Atualizar `CreditCardChargeService::create()`: incluir `purchase_date` no create e usar `Carbon::parse($data['purchase_date'])` para calcular datas de vencimento das parcelas
- [x] 6.7 Atualizar `FinancialCreditCardChargeFactory.php`: adicionar `purchase_date` com data aleatória recente

## Tarefa 7: Campo purchase_date — Frontend
- [x] 7.1 Atualizar tipo TypeScript `credit-card-charge.ts`: adicionar `purchase_date: string`
- [x] 7.2 Atualizar schema Zod `credit-card-charge-schema.ts`: adicionar validação `purchase_date`
- [x] 7.3 Atualizar `CreditCardChargeForm.vue`: adicionar `purchase_date` ao `initialValues` e campo date no template
- [x] 7.4 Atualizar `Index.vue` (DataTable): adicionar coluna `purchase_date` com formatação brasileira (dd/mm/aaaa)

## Tarefa 8: Campo purchase_date — E2E (Seeder, Page Object, Spec)
- [x] 8.1 Atualizar `E2eTestSeeder.php`: adicionar `purchase_date` aos dados nomeados de charges
- [x] 8.2 Atualizar `CreditCardChargePage.ts`: adicionar `purchase_date` à interface e ao `fillForm`, `isFieldDisabled`
- [x] 8.3 Atualizar `credit-card-charge.spec.ts`: incluir `purchase_date` nos testes de Listing, Creation e Viewing
