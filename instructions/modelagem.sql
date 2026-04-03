SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE financial_accounts (
    uid CHAR(36) PRIMARY KEY,
    user_id BIGINT NOT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('CHECKING','SAVINGS','CASH','OTHER') NOT NULL,
    balance DECIMAL(12,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE financial_categories (
    uid CHAR(36) PRIMARY KEY,
    user_id BIGINT NOT NULL,
    name VARCHAR(100) NOT NULL,
    direction ENUM('INFLOW','OUTFLOW') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE financial_transactions (
    uid CHAR(36) PRIMARY KEY,
    user_id BIGINT NOT NULL,
    financial_account_id CHAR(36) NOT NULL,
    financial_category_id CHAR(36) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    direction ENUM('INFLOW','OUTFLOW') NOT NULL,
    source ENUM('MANUAL','CREDIT_CARD','FIXED','TRANSFER') DEFAULT 'MANUAL',
    description VARCHAR(255),
    occurred_at DATETIME NOT NULL,
    due_date DATE NULL,
    paid_at DATETIME NULL,
    status ENUM('PENDING','PAID','OVERDUE') DEFAULT 'PENDING',
    reference_type VARCHAR(50) NULL,
    reference_id CHAR(36) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (financial_account_id) REFERENCES financial_accounts(uid),
    FOREIGN KEY (financial_category_id) REFERENCES financial_categories(uid)
);

CREATE TABLE financial_transfers (
    uid CHAR(36) PRIMARY KEY,
    user_id BIGINT NOT NULL,
    from_account_id CHAR(36) NOT NULL,
    to_account_id CHAR(36) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    occurred_at DATETIME NOT NULL,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (from_account_id) REFERENCES financial_accounts(uid),
    FOREIGN KEY (to_account_id) REFERENCES financial_accounts(uid)
);

CREATE TABLE financial_fixed_expenses (
    uid CHAR(36) PRIMARY KEY,
    user_id BIGINT NOT NULL,
    financial_category_id CHAR(36) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    description VARCHAR(255) NOT NULL,
    due_day TINYINT NOT NULL,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (financial_category_id) REFERENCES financial_categories(uid)
);

CREATE TABLE financial_credit_cards (
    uid CHAR(36) PRIMARY KEY,
    user_id BIGINT NOT NULL,
    name VARCHAR(100) NOT NULL,
    closing_day TINYINT NOT NULL,
    due_day TINYINT NOT NULL,
    card_type ENUM('PHYSICAL','VIRTUAL') NOT NULL,
    last_four_digits CHAR(4) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE financial_credit_card_charges (
    uid CHAR(36) PRIMARY KEY,
    user_id BIGINT NOT NULL,
    credit_card_id CHAR(36) NOT NULL,
    description VARCHAR(255) NOT NULL,
    total_amount DECIMAL(12,2) NOT NULL,
    installments INT NOT NULL,
    purchase_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (credit_card_id) REFERENCES financial_credit_cards(uid)
);

CREATE TABLE financial_credit_card_installments (
    uid CHAR(36) PRIMARY KEY,
    user_id BIGINT NOT NULL,
    credit_card_charge_id CHAR(36) NOT NULL,
    installment_number INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    due_date DATE NOT NULL,
    financial_transaction_id CHAR(36) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (credit_card_charge_id) REFERENCES financial_credit_card_charges(uid),
    FOREIGN KEY (financial_transaction_id) REFERENCES financial_transactions(uid)
);

CREATE TABLE financial_periods (
    uid CHAR(36) PRIMARY KEY,
    user_id BIGINT NOT NULL,
    month TINYINT NOT NULL,
    year SMALLINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_period (user_id, month, year)
);

SET FOREIGN_KEY_CHECKS = 1;