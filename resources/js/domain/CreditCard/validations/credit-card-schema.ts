import { z } from 'zod';

export const creditCardSchema = z.object({
	name: z.string().min(1, 'Nome é obrigatório').max(100),
	closing_day: z.coerce.number().int().min(1).max(31),
	due_day: z.coerce.number().int().min(1).max(31),
	card_type: z.enum(['PHYSICAL', 'VIRTUAL']),
	last_four_digits: z.string().length(4, 'Deve ter 4 dígitos'),
});

export type CreditCardFormData = z.infer<typeof creditCardSchema>;
