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
