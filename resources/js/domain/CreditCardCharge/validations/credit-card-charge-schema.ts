import { z } from 'zod';

export const creditCardChargeSchema = z.object({
	credit_card_uid: z.string().uuid('Cartão é obrigatório'),
	description: z.string().min(1, 'Descrição é obrigatória').max(255),
	amount: z.coerce.number().positive('Valor deve ser maior que zero'),
	total_installments: z.coerce.number().int().min(1).max(48),
	purchase_date: z.string().min(1, 'Data da compra é obrigatória'),
});

export type CreditCardChargeFormData = z.infer<typeof creditCardChargeSchema>;
