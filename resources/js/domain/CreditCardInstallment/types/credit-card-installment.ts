export interface CreditCardInstallment {
	uid: string;
	installment_number: number;
	amount: number;
	due_date: string;
	transaction?: { uid: string; amount: number; description: string | null };
}
