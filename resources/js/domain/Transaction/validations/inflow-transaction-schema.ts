import { z } from 'zod';

export const inflowTransactionSchema = z.object({
	account_uid: z.string().uuid('Conta é obrigatória'),
	amount: z.coerce.number().positive('Valor deve ser maior que zero'),
	description: z.string().max(255).nullable().optional(),
	occurred_at: z.string().min(1, 'Data é obrigatória'),
	direction: z.literal('INFLOW').default('INFLOW'),
});

export type InflowTransactionFormData = z.infer<typeof inflowTransactionSchema>;
