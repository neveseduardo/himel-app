<template>
	<div class="app-login">
		<Head title="Entrar" />

		<div
			v-if="status"
			class="mb-4 text-center text-sm font-medium text-green-600"
		>
			{{ status }}
		</div>

		<form class="flex flex-col gap-6" @submit.prevent="onSubmit">
			<div class="grid gap-6">
				<Field
					v-slot="{ componentField, errorMessage: emailError }"
					name="email"
				>
					<div class="grid gap-2">
						<Label for="email">Endereço de e-mail</Label>
						<Input
							id="email"
							type="email"
							placeholder="email@exemplo.com"
							autofocus
							:tabindex="1"
							:aria-invalid="!!emailError"
							v-bind="componentField"
						/>
						<p v-if="emailError" class="text-sm text-destructive">
							{{ emailError }}
						</p>
					</div>
				</Field>

				<Field
					v-slot="{ componentField, errorMessage: passwordError }"
					name="password"
				>
					<div class="grid gap-2">
						<div class="flex items-center justify-between">
							<Label for="password">Senha</Label>
							<TextLink
								v-if="canResetPassword"
								:href="request()"
								class="text-sm"
								:tabindex="5"
							>
								Esqueceu sua senha?
							</TextLink>
						</div>
						<InputPassword
							id="password"
							:tabindex="2"
							autocomplete="current-password"
							placeholder="Senha"
							v-bind="componentField"
						/>
						<p
							v-if="passwordError"
							class="text-sm text-destructive"
						>
							{{ passwordError }}
						</p>
					</div>
				</Field>

				<Field v-slot="{ value, handleChange }" name="remember">
					<Label class="flex items-center gap-3">
						<Checkbox
							id="remember"
							:checked="value"
							:tabindex="3"
							@update:checked="handleChange"
						/>
						<span>Lembrar-me</span>
					</Label>
				</Field>

				<Button
					type="submit"
					class="mt-4 w-full"
					:tabindex="4"
					:disabled="isSubmitting"
					data-test="login-button"
				>
					<Spinner v-if="isSubmitting" />
					Entrar
				</Button>
			</div>

			<div
				v-if="canRegister"
				class="text-center text-sm text-muted-foreground"
			>
				Não tem uma conta?
				<TextLink :href="register()" :tabindex="5">
					Cadastre-se
				</TextLink>
			</div>
		</form>
	</div>
</template>

<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { Field, useForm } from 'vee-validate';
import { watch } from 'vue';
import { toast } from 'vue-sonner';

import { loginSchema } from '@/lib/validations/auth-schemas';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

const props = defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
    errors?: Record<string, string[]>;
}>();

const { handleSubmit, isSubmitting, setErrors } = useForm({
	validationSchema: toTypedSchema(loginSchema),
	initialValues: {
		email: 'test@example.com',
		password: 'password',
		remember: false,
	},
});

watch(
	() => props.errors,
	(errors) => {
		if (errors && Object.keys(errors).length > 0) {
			const flatErrors: Record<string, string> = {};
			for (const [key, value] of Object.entries(errors)) {
				if (Array.isArray(value) && value.length > 0) {
					flatErrors[key] = value[0];
				} else if (typeof value === 'string') {
					flatErrors[key] = value;
				}
			}
			setErrors(flatErrors);

			const errorMessage =
                flatErrors.email ||
                flatErrors.password ||
                'Credenciais inválidas';
			toast.error('Erro no login', {
				description: errorMessage,
			});
		}
	},
	{ immediate: true }
);

const onSubmit = handleSubmit((values) => {
	router.post(store.form().action, values);
});
</script>
