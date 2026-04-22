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
	total_fixed_expenses?: number;
	total_credit_card_installments?: number;
	total_manual?: number;
	total_transfer?: number;
}

export interface InitializationResult {
	fixed_created: number;
	installments_linked: number;
	installments_created: number;
	skipped: number;
}

export interface PeriodFixedExpenseItem {
	transaction_uid: string;
	description: string | null;
	amount: number;
	due_day: number | null;
	category_name: string | null;
}

export interface PeriodFixedExpenses {
	items: PeriodFixedExpenseItem[];
	subtotal: number;
}

export interface PeriodInstallmentItem {
	transaction_uid: string;
	charge_description: string | null;
	amount: number;
	due_date: string | null;
	installment_number: number | null;
	total_installments: number | null;
	credit_card_name: string | null;
}

export interface PeriodInstallments {
	items: PeriodInstallmentItem[];
	subtotal: number;
}

export interface CardBreakdownItem {
	credit_card_name: string;
	credit_card_uid: string;
	total: number;
}

export interface PeriodCardBreakdown {
	cards: CardBreakdownItem[];
	grand_total: number;
}
