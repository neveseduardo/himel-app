import { z } from 'zod';

export const transactionSchema = z.object({
	account_uid: z.string().uuid('Conta é obrigatória'),
	category_uid: z.string().uuid('Categoria é obrigatória'),
	amount: z.coerce.number().positive('Valor deve ser maior que zero'),
	direction: z.enum(['INFLOW', 'OUTFLOW']),
	status: z.enum(['PENDING', 'PAID', 'OVERDUE']),
	source: z.enum(['MANUAL', 'CREDIT_CARD', 'FIXED', 'TRANSFER']).default('MANUAL'),
	description: z.string().max(255).nullable().optional(),
	occurred_at: z.string().min(1, 'Data é obrigatória'),
	due_date: z.string().nullable().optional(),
	paid_at: z.string().nullable().optional(),
	period_uid: z.string().uuid().nullable().optional(),
});

export type TransactionFormData = z.infer<typeof transactionSchema>;
