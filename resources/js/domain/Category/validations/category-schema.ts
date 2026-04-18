import { z } from 'zod';

export const categorySchema = z.object({
	name: z.string().min(1, 'Nome é obrigatório').max(100),
	direction: z.enum(['INFLOW', 'OUTFLOW']),
});

export type CategoryFormData = z.infer<typeof categorySchema>;
