export interface CategoryServicePort {
	/** Busca lista paginada de categorias */
	fetchAll(filters?: Record<string, string>): Promise<void>;

	/** Cria uma nova categoria */
	create(data: { name: string; direction: string }): Promise<void>;

	/** Atualiza uma categoria existente */
	update(uid: string, data: { name: string; direction: string }): Promise<void>;

	/** Remove uma categoria */
	destroy(uid: string): Promise<void>;
}
