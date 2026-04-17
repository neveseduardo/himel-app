export interface CreditCardCharge {
	uid: string;
	description: string;
	total_amount: number;
	installments: number;
	purchase_date: string;
	credit_card?: { uid: string; name: string };
}
