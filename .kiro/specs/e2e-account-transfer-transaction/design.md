# Documento de Design — Testes E2E: Account, Transfer e Transaction

## Visão Geral

Este documento descreve o design técnico para implementação dos testes E2E dos módulos Account, Transfer e Transaction. Segue os padrões já estabelecidos nos módulos CreditCard, CreditCardCharge e FixedExpense: Page Object Pattern, seeder idempotente, `waitForResponse()` para sincronização, e organização por `test.describe`.

## Decisões de Design

### Abordagem Geral
- Reutilizar os mesmos padrões de Page Object, seeder e organização de testes dos módulos anteriores
- Todos os testes são example-based E2E (sem PBT — operações de alto custo com banco real)
- Testes read-only (Listing, Search, Pagination, Dialog Reopen) executam antes de testes de mutação (Creation, Editing, Viewing, Deletion)

### Factories
- Mesmo bug das specs anteriores: factories sem `protected $model` definido
- Correção idêntica: adicionar `protected $model = Model::class` e import correspondente
- `FinancialAccountFactory` também referencia `Account::getTypes()` sem import — corrigir

### Seeder — Ordem de Dependências
- Accounts devem ser criados primeiro (Transfer e Transaction dependem de accounts)
- Transfers criados segundo (dependem de accounts)
- Transactions criados terceiro (dependem de accounts e categories)
- Reset na ordem inversa: Transactions → Transfers → Accounts
- Factory de Transfer precisa receber `from_account_uid` e `to_account_uid` de contas do usuário de teste
- Factory de Transaction precisa receber `account_uid` e `category_uid` do usuário de teste

### Dialog Sync
- Mesmo padrão do CreditCard/FixedExpense: adicionar `@update:open` no ModalDialog de cada Index.vue
- Handler chama `store.closeModal()` quando recebe `false`
- ModalDialog.vue já emite `update:open` (corrigido na spec anterior)

### Transfer — Sem Edição
- Rotas web excluem `show`, `create`, `edit`, `update`
- Apenas 2 botões de ação por linha: Eye (view) e Trash (delete)
- TransferPage_PO não terá `clickEditButton()`
- Testes de edição não existem (diferente de Account e Transaction)
- Botão de submit no formulário sempre diz "Criar" (sem modo edit)

### Transaction — Formulário Complexo
- 4 campos Select (account_uid, category_uid, direction, status) + campos de texto/data
- Campo `source` não aparece no formulário (default MANUAL)
- `fillForm()` precisa lidar com múltiplos Selects sequencialmente
- Selects indexados por posição no dialog (nth) para distinguir entre eles


### Transfer — Busca
- TransferService filtra por `account_uid`, `date_from`, `date_to` mas NÃO tem filtro `search`
- TransferPageController passa apenas `['page', 'per_page', 'account_uid', 'date_from', 'date_to']` para o service
- A FilterBar no frontend envia `search` mas o backend ignora — busca não funciona para Transfer
- Testes de Search para Transfer devem ser adaptados conforme comportamento real

## Arquivos a Criar/Alterar

### Fase 1: Infraestrutura Compartilhada

| Arquivo | Ação | Descrição |
|---------|------|-----------|
| `database/factories/FinancialAccountFactory.php` | Alterar | Adicionar `protected $model` e import do Account |
| `database/factories/FinancialTransferFactory.php` | Alterar | Adicionar `protected $model` e import do Transfer |
| `database/factories/FinancialTransactionFactory.php` | Alterar | Adicionar `protected $model` e import do Transaction |
| `database/seeders/E2eTestSeeder.php` | Alterar | Adicionar métodos reset/seed para Account, Transfer, Transaction |
| `resources/js/pages/finance/accounts/Index.vue` | Alterar | Adicionar `@update:open` no ModalDialog |
| `resources/js/pages/finance/transfers/Index.vue` | Alterar | Adicionar `@update:open` no ModalDialog |
| `resources/js/pages/finance/transactions/Index.vue` | Alterar | Adicionar `@update:open` no ModalDialog |

### Fase 2: Account E2E

| Arquivo | Ação | Descrição |
|---------|------|-----------|
| `e2e/pages/AccountPage.ts` | Criar | Page Object com interface AccountFormData |
| `e2e/tests/account.spec.ts` | Criar | ~28 testes (Listing, Search, Pagination, Dialog Reopen, Creation, Editing, Viewing, Deletion) |

### Fase 3: Transfer E2E

| Arquivo | Ação | Descrição |
|---------|------|-----------|
| `e2e/pages/TransferPage.ts` | Criar | Page Object com interface TransferFormData |
| `e2e/tests/transfer.spec.ts` | Criar | ~22 testes (Listing, Search, Pagination, Dialog Reopen, Creation, Viewing, Deletion — sem Editing) |

### Fase 4: Transaction E2E

| Arquivo | Ação | Descrição |
|---------|------|-----------|
| `e2e/pages/TransactionPage.ts` | Criar | Page Object com interface TransactionFormData |
| `e2e/tests/transaction.spec.ts` | Criar | ~28 testes (Listing, Search, Pagination, Dialog Reopen, Creation, Editing, Viewing, Deletion) |

## Detalhes Técnicos por Componente

### FinancialAccountFactory — Correção

Adicionar `protected $model = Account::class` e import `use App\Domain\Account\Models\Account`.

### FinancialTransferFactory — Correção

Adicionar `protected $model = Transfer::class` (import do Transfer já existe).

### FinancialTransactionFactory — Correção

Adicionar `protected $model = Transaction::class` (import do Transaction já existe).

### E2eTestSeeder — Novos Métodos

Ordem no `run()`:
1. `ensureDefaultCategories($user)`
2. `resetTransactions($user)` — FK: depende de accounts
3. `resetTransfers($user)` — FK: depende de accounts
4. `resetAccounts($user)` — base
5. `seedNamedAccounts($user)`
6. `seedFactoryAccounts($user)`
7. `seedNamedTransfers($user)` — depende de accounts
8. `seedFactoryTransfers($user)`
9. `seedNamedTransactions($user)` — depende de accounts + categories
10. `seedFactoryTransactions($user)`
11. (existentes: CreditCard, CreditCardCharge, FixedExpense)

### AccountPage Page Object

Interface `AccountFormData`: `name` (string), `type` ('CHECKING'|'SAVINGS'|'CASH'|'OTHER'), `balance` (number).
- `fillForm()`: preenche name (text), type (Select: "Conta Corrente"/"Poupança"/"Dinheiro"/"Outro"), balance (number)
- Botões de ação: [0] Eye, [1] Pencil, [2] Trash

### TransferPage Page Object

Interface `TransferFormData`: `from_account_uid` (string), `to_account_uid` (string), `amount` (number), `occurred_at` (string), `description` (string).
- `fillForm()`: preenche 2 Selects sequenciais (contas), amount, occurred_at, description
- Botões de ação: [0] Eye, [1] Trash (apenas 2 — sem Pencil)

### TransactionPage Page Object

Interface `TransactionFormData`: `account_uid` (string), `category_uid` (string), `amount` (number), `direction` ('INFLOW'|'OUTFLOW'), `status` ('PENDING'|'PAID'), `description` (string), `occurred_at` (string), `due_date` (string, opcional), `paid_at` (string, opcional).
- `fillForm()`: preenche 4 Selects (account, category, direction, status) + campos texto/data
- Botões de ação: [0] Eye, [1] Pencil, [2] Trash

### Dialog Sync — Padrão de Correção

Para cada Index.vue, adicionar handler `@update:open` no ModalDialog:
```vue
@update:open="(open: boolean) => { if (!open) store.closeModal() }"
```

## Propriedades de Corretude

Não aplicável — todos os testes são example-based E2E. PBT não é adequado para testes E2E que dependem de banco de dados real, servidor web e browser.