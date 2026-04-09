# Modelo de Dados — Himel App

> **Glob:** `database/migrations/**/*.php, app/Domain/**/Models/*.php`
>
> Schema completo do banco de dados. Consultar ao criar migrations, models ou queries.

## financial_accounts

| Coluna | Tipo | Constraints |
|--------|------|-------------|
| `uid` | CHAR(36) | PRIMARY KEY |
| `user_id` | BIGINT | NOT NULL |
| `name` | VARCHAR(100) | NOT NULL |
| `type` | ENUM('CHECKING','SAVINGS','CASH','OTHER') | NOT NULL |
| `balance` | DECIMAL(12,2) | DEFAULT 0 |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

## financial_categories

| Coluna | Tipo | Constraints |
|--------|------|-------------|
| `uid` | CHAR(36) | PRIMARY KEY |
| `user_id` | BIGINT | NOT NULL |
| `name` | VARCHAR(100) | NOT NULL |
| `direction` | ENUM('INFLOW','OUTFLOW') | NOT NULL |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

## financial_transactions

| Coluna | Tipo | Constraints |
|--------|------|-------------|
| `uid` | CHAR(36) | PRIMARY KEY |
| `user_id` | BIGINT | NOT NULL |
| `financial_account_id` | CHAR(36) | NOT NULL, FK → `financial_accounts.uid` |
| `financial_category_id` | CHAR(36) | NOT NULL, FK → `financial_categories.uid` |
| `period_uid` | CHAR(36) | NULLABLE, FK → `financial_periods.uid` ON DELETE SET NULL |
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
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

## financial_transfers

| Coluna | Tipo | Constraints |
|--------|------|-------------|
| `uid` | CHAR(36) | PRIMARY KEY |
| `user_id` | BIGINT | NOT NULL |
| `from_account_id` | CHAR(36) | NOT NULL, FK → `financial_accounts.uid` |
| `to_account_id` | CHAR(36) | NOT NULL, FK → `financial_accounts.uid` |
| `amount` | DECIMAL(12,2) | NOT NULL |
| `occurred_at` | DATETIME | NOT NULL |
| `description` | VARCHAR(255) | NULLABLE |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

## financial_fixed_expenses

| Coluna | Tipo | Constraints |
|--------|------|-------------|
| `uid` | CHAR(36) | PRIMARY KEY |
| `user_id` | BIGINT | NOT NULL |
| `financial_category_id` | CHAR(36) | NOT NULL, FK → `financial_categories.uid` |
| `amount` | DECIMAL(12,2) | NOT NULL |
| `description` | VARCHAR(255) | NOT NULL |
| `due_day` | TINYINT | NOT NULL |
| `active` | BOOLEAN | DEFAULT TRUE |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

## financial_credit_cards

| Coluna | Tipo | Constraints |
|--------|------|-------------|
| `uid` | CHAR(36) | PRIMARY KEY |
| `user_id` | BIGINT | NOT NULL |
| `name` | VARCHAR(100) | NOT NULL |
| `closing_day` | TINYINT | NOT NULL |
| `due_day` | TINYINT | NOT NULL |
| `card_type` | ENUM('PHYSICAL','VIRTUAL') | NOT NULL |
| `last_four_digits` | CHAR(4) | NOT NULL |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

## financial_credit_card_charges

| Coluna | Tipo | Constraints |
|--------|------|-------------|
| `uid` | CHAR(36) | PRIMARY KEY |
| `user_id` | BIGINT | NOT NULL |
| `credit_card_id` | CHAR(36) | NOT NULL, FK → `financial_credit_cards.uid` |
| `description` | VARCHAR(255) | NOT NULL |
| `total_amount` | DECIMAL(12,2) | NOT NULL |
| `installments` | INT | NOT NULL |
| `purchase_date` | DATE | NOT NULL |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

## financial_credit_card_installments

| Coluna | Tipo | Constraints |
|--------|------|-------------|
| `uid` | CHAR(36) | PRIMARY KEY |
| `user_id` | BIGINT | NOT NULL |
| `credit_card_charge_id` | CHAR(36) | NOT NULL, FK → `financial_credit_card_charges.uid` |
| `installment_number` | INT | NOT NULL |
| `amount` | DECIMAL(12,2) | NOT NULL |
| `due_date` | DATE | NOT NULL |
| `financial_transaction_id` | CHAR(36) | NULLABLE, FK → `financial_transactions.uid` |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

## financial_periods

| Coluna | Tipo | Constraints |
|--------|------|-------------|
| `uid` | CHAR(36) | PRIMARY KEY |
| `user_id` | BIGINT | NOT NULL |
| `month` | TINYINT | NOT NULL |
| `year` | SMALLINT | NOT NULL |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

**Unique Index:** `unique_user_period (user_id, month, year)`

## Relacionamentos

```
User 1──N financial_accounts
User 1──N financial_categories
User 1──N financial_transactions
User 1──N financial_transfers
User 1──N financial_credit_cards
User 1──N financial_periods

financial_accounts 1──N financial_transactions (financial_account_id)
financial_categories 1──N financial_transactions (financial_category_id)
financial_categories 1──N financial_fixed_expenses (financial_category_id)
financial_periods 1──N financial_transactions (period_uid, nullable)

financial_accounts 1──N financial_transfers (from_account_id)
financial_accounts 1──N financial_transfers (to_account_id)

financial_credit_cards 1──N financial_credit_card_charges (credit_card_id)
financial_credit_card_charges 1──N financial_credit_card_installments (credit_card_charge_id)
financial_credit_card_installments N──1 financial_transactions (financial_transaction_id)
```
