import { z } from 'zod';

export const loginSchema = z.object({
	email: z
		.string()
		.min(1, 'O e-mail é obrigatório.')
		.email('Informe um e-mail válido.'),
	password: z
		.string()
		.min(1, 'A senha é obrigatória.')
		.min(8, 'A senha deve ter pelo menos 8 caracteres.'),
	remember: z.boolean().optional(),
});

export const registerSchema = z
	.object({
		name: z
			.string()
			.min(1, 'O nome é obrigatório.')
			.max(255, 'O nome não pode ter mais de 255 caracteres.'),
		email: z
			.string()
			.min(1, 'O e-mail é obrigatório.')
			.email('Informe um e-mail válido.'),
		password: z
			.string()
			.min(1, 'A senha é obrigatória.')
			.min(8, 'A senha deve ter pelo menos 8 caracteres.')
			.max(72, 'A senha não pode ter mais de 72 caracteres.'),
		password_confirmation: z
			.string()
			.min(1, 'A confirmação de senha é obrigatória.'),
	})
	.refine((data) => data.password === data.password_confirmation, {
		message: 'As senhas não conferem.',
		path: ['password_confirmation'],
	});

export const forgotPasswordSchema = z.object({
	email: z
		.string()
		.min(1, 'O e-mail é obrigatório.')
		.email('Informe um e-mail válido.'),
});

export const resetPasswordSchema = z
	.object({
		email: z
			.string()
			.min(1, 'O e-mail é obrigatório.')
			.email('Informe um e-mail válido.'),
		password: z
			.string()
			.min(1, 'A senha é obrigatória.')
			.min(8, 'A senha deve ter pelo menos 8 caracteres.')
			.max(72, 'A senha não pode ter mais de 72 caracteres.'),
		password_confirmation: z
			.string()
			.min(1, 'A confirmação de senha é obrigatória.'),
	})
	.refine((data) => data.password === data.password_confirmation, {
		message: 'As senhas não conferem.',
		path: ['password_confirmation'],
	});

export const twoFactorChallengeSchema = z
	.object({
		code: z
			.string()
			.min(1, 'O código é obrigatório.')
			.length(6, 'O código deve ter 6 dígitos.')
			.or(z.literal('')),
		recovery_code: z.string().optional(),
	})
	.refine((data) => data.code || data.recovery_code, {
		message: 'Informe o código de autenticação ou o código de recuperação.',
		path: ['code'],
	});

export const confirmPasswordSchema = z.object({
	password: z.string().min(1, 'A senha é obrigatória.'),
});

export type LoginFormData = z.infer<typeof loginSchema>;
export type RegisterFormData = z.infer<typeof registerSchema>;
export type ForgotPasswordFormData = z.infer<typeof forgotPasswordSchema>;
export type ResetPasswordFormData = z.infer<typeof resetPasswordSchema>;
export type TwoFactorChallengeFormData = z.infer<
    typeof twoFactorChallengeSchema
>;
export type ConfirmPasswordFormData = z.infer<typeof confirmPasswordSchema>;
