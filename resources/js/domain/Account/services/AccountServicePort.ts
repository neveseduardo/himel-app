export interface AccountServicePort {
	/** Busca lista paginada de contas */
	fetchAll(filters?: Record<string, string>): Promise<void>;

	/** Cria uma nova conta */
	create(data: { name: string; type: string; balance?: number }): Promise<void>;

	/** Atualiza uma conta existente */
	update(uid: string, data: { name: string; type: string; balance?: number }): Promise<void>;

	/** Remove uma conta */
	destroy(uid: string): Promise<void>;
}
