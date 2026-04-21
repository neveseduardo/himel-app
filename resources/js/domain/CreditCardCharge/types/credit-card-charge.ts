export interface CreditCardCharge {
	uid: string;
	description: string;
	amount: number;
	total_installments: number;
	purchase_date: string;
	credit_card?: { uid: string; name: string };
}
