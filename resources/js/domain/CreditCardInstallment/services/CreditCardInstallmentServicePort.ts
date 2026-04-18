export interface CreditCardInstallmentServicePort {
	/** Busca lista de parcelas */
	fetchAll(filters?: Record<string, string>): Promise<void>;

	/** Cria uma nova parcela */
	create(data: Record<string, unknown>): Promise<void>;

	/** Busca detalhes de uma parcela */
	show(uid: string): Promise<void>;

	/** Atualiza uma parcela existente */
	update(uid: string, data: Record<string, unknown>): Promise<void>;

	/** Remove uma parcela */
	destroy(uid: string): Promise<void>;

	/** Marca uma parcela como paga */
	markAsPaid(uid: string): Promise<void>;
}
