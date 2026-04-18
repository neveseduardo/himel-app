export interface TransferServicePort {
	/** Busca lista paginada de transferências */
	fetchAll(filters?: Record<string, string>): Promise<void>;

	/** Cria uma nova transferência */
	create(data: {
		amount: number;
		occurred_at: string;
		description?: string;
		from_account_uid: string;
		to_account_uid: string;
	}): Promise<void>;

	/** Remove uma transferência */
	destroy(uid: string): Promise<void>;
}
