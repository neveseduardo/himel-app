# Documento de Design — E2E FixedExpense

## Visão Geral

Este documento detalha o design técnico para implementação de testes E2E Playwright cobrindo o CRUD completo do módulo FixedExpense. Inclui 4 entregas: (1) correção da factory, (2) atualização do seeder, (3) dialog sync fix no Index.vue, (4) Page Object + spec de testes.

---

## Item 1: Correção da Factory FinancialFixedExpenseFactory

### Problema

O model `FixedExpense` referencia `Database\Factories\FixedExpenseFactory` via PHPDoc, mas o arquivo real é `FinancialFixedExpenseFactory`. Sem `protected $model`, o Laravel não consegue resolver o model automaticamente pela convenção de nomes.

### Solução

**Arquivo:** `database/factories/FinancialFixedExpenseFactory.php`

Adicionar a propriedade `protected $model`:

```php
class FinancialFixedExpenseFactory extends Factory
{
    protected $model = FixedExpense::class;

    public function definition(): array
    {
        // ... (sem alterações)
    }
}
```

---

## Item 2: Atualização do E2eTestSeeder

### Solução

**Arquivo:** `database/seeders/E2eTestSeeder.php`

Adicionar 3 novos métodos privados e chamá-los no `run()`:

#### Método `resetFixedExpenses(User $user)`
```php
private function resetFixedExpenses(User $user): void
{
    FixedExpense::where('user_uid', $user->uid)->delete();
}
```

#### Método `seedNamedFixedExpenses(User $user)`

Busca a primeira categoria OUTFLOW do usuário e cria 3 registros:

```php
private function seedNamedFixedExpenses(User $user): void
{
    $category = Category::where('user_uid', $user->uid)
        ->where('direction', 'OUTFLOW')
        ->first();

    $expenses = [
        ['name' => 'Aluguel', 'amount' => 1500.00, 'due_day' => 10, 'active' => true, 'category_uid' => $category->uid],
        ['name' => 'Internet', 'amount' => 120.00, 'due_day' => 15, 'active' => true, 'category_uid' => $category->uid],
        ['name' => 'Academia', 'amount' => 89.90, 'due_day' => 5, 'active' => false, 'category_uid' => $category->uid],
    ];

    foreach ($expenses as $expense) {
        FixedExpense::create(array_merge($expense, ['user_uid' => $user->uid]));
    }
}
```

#### Método `seedFactoryFixedExpenses(User $user)`

```php
private function seedFactoryFixedExpenses(User $user): void
{
    $category = Category::where('user_uid', $user->uid)
        ->where('direction', 'OUTFLOW')
        ->first();

    FinancialFixedExpenseFactory::new()
        ->count(20)
        ->create([
            'user_uid' => $user->uid,
            'category_uid' => $category->uid,
        ]);
}
```

#### Atualização do `run()`

Adicionar chamadas após os blocos de CreditCardCharge:

```php
$this->resetFixedExpenses($user);
$this->seedNamedFixedExpenses($user);
$this->seedFactoryFixedExpenses($user);
```

### Imports adicionais

```php
use App\Domain\Category\Models\Category;
use App\Domain\FixedExpense\Models\FixedExpense;
use Database\Factories\FinancialFixedExpenseFactory;
```

---

## Item 3: Dialog Sync Fix para FixedExpense Index.vue

### Problema

O `FixedExpense Index.vue` usa `ModalDialog` com `ref` e `watch` para abrir/fechar, mas não escuta o evento `update:open` emitido quando o diálogo fecha via X/ESC/overlay. Resultado: `store.isModalOpen` permanece `true` e o modal não reabre.

### Solução

**Arquivo:** `resources/js/pages/finance/fixed-expenses/Index.vue`

1. Adicionar handler:
```ts
function handleDialogOpenChange(open: boolean) {
    if (!open && store.isModalOpen) {
        store.closeModal();
    }
}
```

2. Adicionar `@update:open` ao `<ModalDialog>`:
```vue
<ModalDialog ref="modalRef" :title="modalTitle" @update:open="handleDialogOpenChange">
```

---

## Item 4: Page Object — FixedExpensePage.ts

### Arquivo: `e2e/pages/FixedExpensePage.ts`

Segue o padrão exato de `CreditCardPage.ts` e `CreditCardChargePage.ts`, com adaptações para os campos específicos do FixedExpense.

### Interface

```typescript
export interface FixedExpenseFormData {
    description: string;
    amount: number;
    due_day: number;
    category_uid: string;  // nome da categoria para seleção no combobox
    active: boolean;
}
```

### Métodos — Diferenças em relação ao CreditCardPage

| Método | Diferença |
|---|---|
| `goto()` | URL: `/finance/fixed-expenses` |
| `getPageTitle()` | Heading: "Despesas Fixas" |
| `getRowByDescription(desc)` | Mesmo padrão de `getRowByName` mas com nome semântico |
| `search(term)` / `clearSearch()` | URL pattern: `fixed-expenses` |
| `fillForm(data)` | Campos: description (input), amount (input number), due_day (input number), category_uid (combobox Select), active (checkbox) |
| `isFieldDisabled(field)` | Campos especiais: `category_uid` → combobox, `active` → checkbox |
| `getFormFieldValue(field)` | Campos especiais: `category_uid` → combobox innerText, `active` → checkbox checked state |
| `getValidationError(field)` | Labels: description→"Descrição", amount→"Valor", due_day→"Dia Vencimento", category_uid→"Categoria" |

### fillForm — Detalhes

```typescript
async fillForm(data: FixedExpenseFormData): Promise<void> {
    const dialog = this.page.getByRole('dialog');

    // Text input
    await dialog.locator('[name="description"]').fill(data.description);
    // Number inputs
    await dialog.locator('[name="amount"]').fill(String(data.amount));
    await dialog.locator('[name="due_day"]').fill(String(data.due_day));

    // Category select (reka-ui Select combobox)
    await dialog.getByRole('combobox').click();
    await this.page.getByRole('option', { name: new RegExp(data.category_uid) }).click();

    // Active checkbox
    const checkbox = dialog.locator('#active');
    const isChecked = await checkbox.isChecked();
    if (isChecked !== data.active) {
        await checkbox.click();
    }
}
```

### isFieldDisabled — Detalhes para campos especiais

```typescript
async isFieldDisabled(field: string): Promise<boolean> {
    const dialog = this.page.getByRole('dialog');

    if (field === 'category_uid') {
        return dialog.getByRole('combobox').isDisabled();
    }

    if (field === 'active') {
        return dialog.locator('#active').isDisabled();
    }

    return dialog.locator(`[name="${field}"]`).isDisabled();
}
```

### Pagination — URL pattern

Todos os métodos de paginação usam `fixed-expenses` como URL pattern no `waitForResponse`.

---

## Item 5: Spec de Testes — fixed-expense.spec.ts

### Arquivo: `e2e/tests/fixed-expense.spec.ts`

Segue a estrutura exata de `credit-card.spec.ts` com 8 blocos `test.describe`:

1. **FixedExpense Listing** (3 testes)
   - Título da página
   - Registros semeados visíveis (busca individual por "Aluguel", "Internet", "Academia")
   - Conteúdo das linhas: descrição, valor formatado ("1.500,00"), dia vencimento, status Badge ("Ativa"/"Inativa")

2. **FixedExpense Search and Filtering** (3 testes)
   - Busca filtra corretamente
   - Limpar busca retorna todos
   - Busca sem resultado mostra empty state

3. **FixedExpense Pagination** (5 testes)
   - Controles visíveis
   - Próxima funciona
   - Anterior funciona
   - Anterior desabilitado na primeira página
   - Próxima desabilitado na última página

4. **FixedExpense Dialog Reopen** (2 testes)
   - Reabertura via ESC
   - Reabertura via overlay

5. **FixedExpense Creation** (5 testes)
   - Modal abre com título correto
   - Preenchimento + submit mostra toast
   - Novo registro aparece na DataTable
   - Dados inválidos mostram erros de validação
   - Cancelar fecha modal

6. **FixedExpense Editing** (4 testes)
   - Modal abre com título "Editar Despesa Fixa"
   - Campos pré-preenchidos
   - Modificação + submit mostra toast
   - DataTable reflete atualização

7. **FixedExpense Viewing** (3 testes)
   - Modal abre com título "Detalhes da Despesa Fixa"
   - Campos desabilitados (description, amount, due_day, category_uid, active)
   - Sem botão submit

8. **FixedExpense Deletion** (3 testes)
   - Popover de confirmação visível
   - Confirmação mostra toast
   - Registro removido da DataTable

### Dados de teste para criação

```typescript
// Teste de criação com sucesso
{
    description: 'Despesa Teste E2E',
    amount: 250.00,
    due_day: 20,
    category_uid: '<primeira categoria OUTFLOW visível>',
    active: true,
}

// Teste de criação para verificar na DataTable
{
    description: 'Despesa Nova Listagem',
    amount: 450.00,
    due_day: 25,
    category_uid: '<primeira categoria OUTFLOW visível>',
    active: true,
}
```

### Notas sobre o campo `active`

- Na listagem, o campo `active` é renderizado como Badge: "Ativa" (variant default) ou "Inativa" (variant secondary)
- No teste de listagem, verificar `toContainText('Ativa')` para registros ativos e `toContainText('Inativa')` para "Academia"
- No teste de visualização, verificar que o checkbox `#active` está desabilitado
- No `fillForm`, o checkbox precisa de lógica condicional: verificar estado atual e clicar apenas se diferente do desejado

### Notas sobre o campo `category_uid`

- O formulário usa reka-ui Select com combobox
- No `fillForm`, clicar no combobox e selecionar a opção pelo nome da categoria (não pelo UID)
- No `isFieldDisabled`, verificar via `getByRole('combobox').isDisabled()`
- Para testes de criação, usar o nome da primeira categoria OUTFLOW disponível no select

### Notas sobre toast messages

- Criação: "Despesa fixa criado(a) com sucesso!"
- Edição: "Despesa fixa atualizado(a) com sucesso!"
- Deleção: "Despesa fixa excluído(a) com sucesso!"

Estes são gerados pelo composable `useCrudToast('Despesa fixa')`.
