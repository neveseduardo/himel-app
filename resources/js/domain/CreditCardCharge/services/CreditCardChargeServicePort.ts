export interface CreditCardChargeServicePort {
	/** Busca lista paginada de cobranças de cartão */
	fetchAll(filters?: Record<string, string>): Promise<void>;

	/** Cria uma nova cobrança de cartão */
	create(data: {
		description: string;
		total_amount: number;
		installments: number;
		purchase_date: string;
		credit_card_uid: string;
	}): Promise<void>;
}
