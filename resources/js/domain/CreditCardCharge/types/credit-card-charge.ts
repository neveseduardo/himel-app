export interface CreditCardCharge {
	uid: string;
	description: string;
	amount: number;
	total_installments: number;
	credit_card?: { uid: string; name: string };
}
