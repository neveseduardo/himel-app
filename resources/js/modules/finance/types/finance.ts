export type AccountType = 'CHECKING' | 'SAVINGS' | 'CASH' | 'OTHER';
export type Direction = 'INFLOW' | 'OUTFLOW';
export type TransactionStatus = 'PENDING' | 'PAID' | 'OVERDUE';
export type TransactionSource = 'MANUAL' | 'CREDIT_CARD' | 'FIXED' | 'TRANSFER';
export type CardType = 'PHYSICAL' | 'VIRTUAL';

export interface Account {
	uid: string;
	name: string;
	type: AccountType;
	balance: number;
	created_at: string;
}

export interface Category {
	uid: string;
	name: string;
	direction: Direction;
	created_at: string;
}

export interface Transaction {
	uid: string;
	amount: number;
	direction: Direction;
	status: TransactionStatus;
	source: TransactionSource;
	description: string | null;
	occurred_at: string;
	due_date: string | null;
	paid_at: string | null;
	account?: Account;
	category?: Category;
}

export interface Transfer {
	uid: string;
	amount: number;
	occurred_at: string;
	description: string | null;
	from_account?: Account;
	to_account?: Account;
}

export interface FixedExpense {
	uid: string;
	description: string;
	amount: number;
	due_day: number;
	active: boolean;
	category?: Category;
}

export interface CreditCard {
	uid: string;
	name: string;
	closing_day: number;
	due_day: number;
	card_type: CardType;
	last_four_digits: string;
}

export interface CreditCardCharge {
	uid: string;
	description: string;
	total_amount: number;
	installments: number;
	purchase_date: string;
	credit_card?: CreditCard;
}

export interface CreditCardInstallment {
	uid: string;
	installment_number: number;
	amount: number;
	due_date: string;
	transaction?: Transaction;
}

export interface Period {
	uid: string;
	month: number;
	year: number;
	transactions_count?: number;
}

export interface PeriodSummary {
	total_inflow: number;
	total_outflow: number;
	balance: number;
}

export interface InitializationResult {
	fixed_created: number;
	installments_linked: number;
	installments_created: number;
	skipped: number;
}

export interface PaginationMeta {
	current_page: number;
	per_page: number;
	total: number;
	last_page: number;
}
