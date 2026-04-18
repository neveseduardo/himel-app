export interface CreditCardServicePort {
	/** Busca lista paginada de cartões de crédito */
	fetchAll(filters?: Record<string, string>): Promise<void>;

	/** Cria um novo cartão de crédito */
	create(data: {
		name: string;
		closing_day: number;
		due_day: number;
		card_type: string;
		last_four_digits: string;
	}): Promise<void>;

	/** Atualiza um cartão de crédito existente */
	update(uid: string, data: {
		name: string;
		closing_day: number;
		due_day: number;
		card_type: string;
		last_four_digits: string;
	}): Promise<void>;

	/** Remove um cartão de crédito */
	destroy(uid: string): Promise<void>;
}
