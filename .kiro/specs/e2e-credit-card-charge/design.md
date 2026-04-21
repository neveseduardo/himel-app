# Documento de Design — E2E CreditCardCharge (Incremento)

## Visão Geral

Este documento detalha o design técnico para dois incrementos no módulo CreditCardCharge:
1. Correção de sincronização do modal (dialog sync fix)
2. Adição do campo `purchase_date` em toda a stack

---

## Item 1: Dialog Sync Fix para CreditCardCharge

### Problema

O `ModalDialog.vue` emite `update:open` quando o diálogo fecha via X ou ESC. Porém, o `CreditCardCharge Index.vue` não escuta esse evento. Resultado: `store.isModalOpen` permanece `true` e o modal não reabre no próximo clique.

### Solução

Replicar o padrão já implementado em `CreditCard Index.vue`:

**Arquivo:** `resources/js/pages/finance/credit-card-charges/Index.vue`

1. Adicionar `@update:open` no `<ModalDialog>`:
```vue
<ModalDialog ref="modalRef" :title="modalTitle" @update:open="handleDialogOpenChange">
```

2. Criar handler `handleDialogOpenChange`:
```ts
function handleDialogOpenChange(open: boolean) {
    if (!open && store.isModalOpen) {
        store.closeModal();
    }
}
```

### Testes E2E

**Arquivo:** `e2e/pages/CreditCardChargePage.ts` — adicionar métodos:
- `closeDialogByEsc()`: pressiona ESC e aguarda dialog hidden
- `closeDialogByOverlay()`: clica no overlay e aguarda dialog hidden

**Arquivo:** `e2e/tests/credit-card-charge.spec.ts` — adicionar bloco:
- `CreditCardCharge Dialog Reopen` com 2 testes (ESC e overlay), seguindo o padrão de `credit-card.spec.ts`

---

## Item 2: Campo purchase_date

### Backend

#### Migration

**Novo arquivo:** `database/migrations/YYYY_MM_DD_HHMMSS_add_purchase_date_to_financial_credit_card_charges_table.php`

```php
$table->date('purchase_date')->nullable(); // nullable para registros existentes
```

#### Model

**Arquivo:** `app/Domain/CreditCardCharge/Models/CreditCardCharge.php`

- Adicionar `'purchase_date'` ao array `$fillable`
- Adicionar `'purchase_date' => 'date'` ao array `$casts`

#### Resource

**Arquivo:** `app/Domain/CreditCardCharge/Resources/CreditCardChargeResource.php`

- Adicionar `'purchase_date' => $this->purchase_date?->format('Y-m-d')` ao array de retorno

#### Form Request

**Arquivo:** `app/Domain/CreditCardCharge/Requests/StoreCreditCardChargeRequest.php`

- Adicionar regra: `'purchase_date' => ['required', 'date']`
- Adicionar mensagens de validação correspondentes

#### Service

**Arquivo:** `app/Domain/CreditCardCharge/Services/CreditCardChargeService.php`

Alterar o método `create()`:
- Adicionar `'purchase_date'` ao array de `CreditCardCharge::create()`
- Substituir `now()->addMonths($i)->day($card->due_day)` por:
```php
$purchaseDate = \Carbon\Carbon::parse($data['purchase_date']);
$dueDate = $purchaseDate->copy()->addMonths($i)->day($card->due_day);
```

#### Factory

**Arquivo:** `database/factories/FinancialCreditCardChargeFactory.php`

- Adicionar `'purchase_date' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d')`

### Frontend

#### Tipo TypeScript

**Arquivo:** `resources/js/domain/CreditCardCharge/types/credit-card-charge.ts`

- Adicionar `purchase_date: string;` à interface

#### Schema Zod

**Arquivo:** `resources/js/domain/CreditCardCharge/validations/credit-card-charge-schema.ts`

- Adicionar `purchase_date: z.string().min(1, 'Data da compra é obrigatória')`

#### Formulário

**Arquivo:** `resources/js/domain/CreditCardCharge/components/CreditCardChargeForm.vue`

- Adicionar campo `purchase_date` ao `initialValues` (default: data de hoje formatada `YYYY-MM-DD`)
- Adicionar `<ValidatedField name="purchase_date" label="Data da Compra">` com `<Input type="date">`

#### Página Index (DataTable)

**Arquivo:** `resources/js/pages/finance/credit-card-charges/Index.vue`

- Adicionar coluna `{ key: 'purchase_date', label: 'Data da Compra' }` ao array `columns`
- Adicionar slot `#cell-purchase_date` para formatar a data no padrão brasileiro

### E2E

#### Seeder

**Arquivo:** `database/seeders/E2eTestSeeder.php`

- Adicionar `'purchase_date' => '2024-03-15'` (e datas similares) aos dados nomeados de charges

#### Page Object

**Arquivo:** `e2e/pages/CreditCardChargePage.ts`

- Adicionar `purchase_date: string` à interface `CreditCardChargeFormData`
- Atualizar `fillForm()` para preencher o campo date
- Atualizar `isFieldDisabled()` para incluir `purchase_date`

#### Spec

**Arquivo:** `e2e/tests/credit-card-charge.spec.ts`

- Atualizar testes de Listing para verificar coluna `purchase_date`
- Atualizar testes de Creation para incluir `purchase_date` no `fillForm`
- Atualizar testes de Viewing para verificar `purchase_date` desabilitado
