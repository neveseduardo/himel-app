# Schema do Banco de Dados — SaaS Financeiro Himel App

> **Glob:** `database/migrations/**/*.php, app/Domain/**/Models/*.php`
>
> Este arquivo documenta o schema completo do banco de dados financeiro. O agente DEVE consultar este schema ao criar migrations, models ou queries.

## financial_accounts

Contas financeiras do usuário.

| Coluna | Tipo | Constraints |
|--------|------|-------------|
| `uid` | CHAR(36) | PRIMARY KEY |
| `user_id` | BIGINT | NOT NULL |
| `name` | VARCHAR(100) | NOT NULL |
| `type` | ENUM('CHECKING','SAVINGS','CASH','OTHER') | NOT NULL |
| `balance` | DECIMAL(12,2) | DEFAULT 0 |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |
| `updated_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE |

## financial_categories

Categorias financeiras (entrada/saída).

| Coluna | Tipo | Constraints |
|--------|------|-------------|
| `uid` | CHAR(36) | PRIMARY KEY |
| `user_id` | BIGINT | NOT NULL |
| `name` | VARCHAR(100) | NOT NULL |
| `direction` | ENUM('INFLOW','OUTFLOW') | NOT NULL |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |
| `updated_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE |

## financial_transactions

Transações financeiras vinculadas a contas e categorias.

| Coluna | Tipo | Constraints |
|--------|------|-------------|
| `uid` | CHAR(36) | PRIMARY KEY |
| `user_id` | BIGINT | NOT NULL |
| `financial_account_id` | CHAR(36) | NOT NULL, FK → `financial_accounts.uid` |
| `financial_category_id` | CHAR(36) | NOT NULL, FK → `financial_categories.uid` |
| `amount` | DECIMAL(12,2) | NOT NULL |
| `direction` | ENUM('INFLOW','OUTFLOW') | NOT NULL |
| `source` | ENUM('MANUAL','CREDIT_CARD','FIXED','TRANSFER') | DEFAULT 'MANUAL' |
| `description` | VARCHAR(255) | NULLABLE |
| `occurred_at` | DATETIME | NOT NULL |
| `due_date` | DATE | NULLABLE |
| `paid_at` | DATETIME | NULLABLE |
| `status` | ENUM('PENDING','PAID','OVERDUE') | DEFAULT 'PENDING' |
| `reference_type` | VARCHAR(50) | NULLABLE |
| `reference_id` | CHAR(36) | NULLABLE |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |
| `updated_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE |

**Foreign Keys:**
- `financial_account_id` → `financial_accounts(uid)`
- `financial_category_id` → `financial_categories(uid)`

## financial_transfers

Transferências entre contas.

| Coluna | Tipo | Constraints |
|--------|------|-------------|
| `uid` | CHAR(36) | PRIMARY KEY |
| `user_id` | BIGINT | NOT NULL |
| `from_account_id` | CHAR(36) | NOT NULL, FK → `financial_accounts.uid` |
| `to_account_id` | CHAR(36) | NOT NULL, FK → `financial_accounts.uid` |
| `amount` | DECIMAL(12,2) | NOT NULL |
| `occurred_at` | DATETIME | NOT NULL |
| `description` | VARCHAR(255) | NULLABLE |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |
| `updated_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE |

**Foreign Keys:**
- `from_account_id` → `financial_accounts(uid)`
- `to_account_id` → `financial_accounts(uid)`

## financial_fixed_expenses

Despesas fixas recorrentes.

| Coluna | Tipo | Constraints |
|--------|------|-------------|
| `uid` | CHAR(36) | PRIMARY KEY |
| `user_id` | BIGINT | NOT NULL |
| `financial_category_id` | CHAR(36) | NOT NULL, FK → `financial_categories.uid` |
| `amount` | DECIMAL(12,2) | NOT NULL |
| `description` | VARCHAR(255) | NOT NULL |
| `due_day` | TINYINT | NOT NULL |
| `active` | BOOLEAN | DEFAULT TRUE |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |
| `updated_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE |

**Foreign Keys:**
- `financial_category_id` → `financial_categories(uid)`

## financial_credit_cards

Cartões de crédito do usuário.

| Coluna | Tipo | Constraints |
|--------|------|-------------|
| `uid` | CHAR(36) | PRIMARY KEY |
| `user_id` | BIGINT | NOT NULL |
| `name` | VARCHAR(100) | NOT NULL |
| `closing_day` | TINYINT | NOT NULL |
| `due_day` | TINYINT | NOT NULL |
| `card_type` | ENUM('PHYSICAL','VIRTUAL') | NOT NULL |
| `last_four_digits` | CHAR(4) | NOT NULL |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |
| `updated_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE |

## financial_credit_card_charges

Compras realizadas no cartão de crédito.

| Coluna | Tipo | Constraints |
|--------|------|-------------|
| `uid` | CHAR(36) | PRIMARY KEY |
| `user_id` | BIGINT | NOT NULL |
| `credit_card_id` | CHAR(36) | NOT NULL, FK → `financial_credit_cards.uid` |
| `description` | VARCHAR(255) | NOT NULL |
| `total_amount` | DECIMAL(12,2) | NOT NULL |
| `installments` | INT | NOT NULL |
| `purchase_date` | DATE | NOT NULL |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |
| `updated_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE |

**Foreign Keys:**
- `credit_card_id` → `financial_credit_cards(uid)`

## financial_credit_card_installments

Parcelas de compras no cartão de crédito.

| Coluna | Tipo | Constraints |
|--------|------|-------------|
| `uid` | CHAR(36) | PRIMARY KEY |
| `user_id` | BIGINT | NOT NULL |
| `credit_card_charge_id` | CHAR(36) | NOT NULL, FK → `financial_credit_card_charges.uid` |
| `installment_number` | INT | NOT NULL |
| `amount` | DECIMAL(12,2) | NOT NULL |
| `due_date` | DATE | NOT NULL |
| `financial_transaction_id` | CHAR(36) | NULLABLE, FK → `financial_transactions.uid` |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |
| `updated_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE |

**Foreign Keys:**
- `credit_card_charge_id` → `financial_credit_card_charges(uid)`
- `financial_transaction_id` → `financial_transactions(uid)`

## financial_periods

Períodos (mês/ano) para agrupamento de dashboard.

| Coluna | Tipo | Constraints |
|--------|------|-------------|
| `uid` | CHAR(36) | PRIMARY KEY |
| `user_id` | BIGINT | NOT NULL |
| `month` | TINYINT | NOT NULL |
| `year` | SMALLINT | NOT NULL |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |
| `updated_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE |

**Unique Index:** `unique_user_period (user_id, month, year)`

## Diagrama de Relacionamentos

```
User 1──N financial_accounts
User 1──N financial_categories
User 1──N financial_transactions
User 1──N financial_transfers
User 1──N financial_credit_cards
User 1──N financial_periods

financial_accounts 1──N financial_transactions (via financial_account_id)
financial_categories 1──N financial_transactions (via financial_category_id)
financial_categories 1──N financial_fixed_expenses (via financial_category_id)

financial_accounts 1──N financial_transfers (via from_account_id)
financial_accounts 1──N financial_transfers (via to_account_id)

financial_credit_cards 1──N financial_credit_card_charges (via credit_card_id)
financial_credit_card_charges 1──N financial_credit_card_installments (via credit_card_charge_id)
financial_credit_card_installments N──1 financial_transactions (via financial_transaction_id)
```
