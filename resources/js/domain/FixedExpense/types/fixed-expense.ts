export interface FixedExpense {
	uid: string;
	description: string;
	amount: number;
	due_day: number;
	active: boolean;
	category?: { uid: string; name: string };
}
