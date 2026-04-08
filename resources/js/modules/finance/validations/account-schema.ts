import { z } from 'zod';

export const accountSchema = z.object({
	name: z.string().min(1, 'Nome é obrigatório').max(100),
	type: z.enum(['CHECKING', 'SAVINGS', 'CASH', 'OTHER']),
	balance: z.coerce.number().min(0).optional().default(0),
});

export type AccountFormData = z.infer<typeof accountSchema>;
