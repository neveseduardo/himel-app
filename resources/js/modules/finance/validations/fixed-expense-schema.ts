import { z } from 'zod';

export const fixedExpenseSchema = z.object({
	description: z.string().min(1, 'Descrição é obrigatória').max(255),
	amount: z.coerce.number().positive('Valor deve ser maior que zero'),
	due_day: z.coerce.number().int().min(1).max(31),
	category_uid: z.string().uuid('Categoria é obrigatória'),
	active: z.boolean().default(true),
});

export type FixedExpenseFormData = z.infer<typeof fixedExpenseSchema>;
