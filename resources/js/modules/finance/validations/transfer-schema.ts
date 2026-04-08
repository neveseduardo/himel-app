import { z } from 'zod';

export const transferSchema = z
	.object({
		from_account_uid: z.string().uuid('Conta de origem é obrigatória'),
		to_account_uid: z.string().uuid('Conta de destino é obrigatória'),
		amount: z.coerce.number().positive('Valor deve ser maior que zero'),
		occurred_at: z.string().min(1, 'Data é obrigatória'),
		description: z.string().max(255).nullable().optional(),
	})
	.refine((data) => data.from_account_uid !== data.to_account_uid, {
		message: 'Contas de origem e destino devem ser diferentes',
		path: ['to_account_uid'],
	});

export type TransferFormData = z.infer<typeof transferSchema>;
