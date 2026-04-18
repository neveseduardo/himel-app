export interface FixedExpenseServicePort {
	/** Busca lista paginada de despesas fixas */
	fetchAll(filters?: Record<string, string>): Promise<void>;

	/** Cria uma nova despesa fixa */
	create(data: {
		description: string;
		amount: number;
		due_day: number;
		category_uid?: string;
	}): Promise<void>;

	/** Atualiza uma despesa fixa existente */
	update(uid: string, data: {
		description: string;
		amount: number;
		due_day: number;
		category_uid?: string;
	}): Promise<void>;

	/** Remove uma despesa fixa */
	destroy(uid: string): Promise<void>;
}
