export interface TransactionServicePort {
	/** Busca lista paginada de transações */
	fetchAll(filters?: Record<string, string>): Promise<void>;

	/** Cria uma nova transação */
	create(data: {
		amount: number;
		direction: string;
		description?: string;
		occurred_at: string;
		due_date?: string;
		account_uid: string;
		category_uid?: string;
	}): Promise<void>;

	/** Atualiza uma transação existente */
	update(uid: string, data: {
		amount: number;
		direction: string;
		description?: string;
		occurred_at: string;
		due_date?: string;
		account_uid: string;
		category_uid?: string;
	}): Promise<void>;

	/** Remove uma transação */
	destroy(uid: string): Promise<void>;
}
