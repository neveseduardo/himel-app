<template>
	<Head title="Cadastro" />

	<Form
		v-slot="{ errors, processing }"
		v-bind="store.form()"
		:reset-on-success="['password', 'password_confirmation']"
		class="flex flex-col gap-6"
	>
		<div class="grid gap-6">
			<div class="grid gap-2">
				<Label for="name">Nome</Label>
				<Input
					id="name"
					type="text"
					required
					autofocus
					:tabindex="1"
					autocomplete="name"
					name="name"
					placeholder="Nome completo"
				/>
				<InputError :message="errors.name" />
			</div>

			<div class="grid gap-2">
				<Label for="email">Endereço de e-mail</Label>
				<Input
					id="email"
					type="email"
					required
					:tabindex="2"
					autocomplete="email"
					name="email"
					placeholder="email@example.com"
				/>
				<InputError :message="errors.email" />
			</div>

			<div class="grid gap-2">
				<Label for="password">Senha</Label>
				<PasswordInput
					id="password"
					required
					:tabindex="3"
					autocomplete="new-password"
					name="password"
					placeholder="Senha"
				/>
				<InputError :message="errors.password" />
			</div>

			<div class="grid gap-2">
				<Label for="password_confirmation">Confirmar senha</Label>
				<PasswordInput
					id="password_confirmation"
					required
					:tabindex="4"
					autocomplete="new-password"
					name="password_confirmation"
					placeholder="Confirmar senha"
				/>
				<InputError :message="errors.password_confirmation" />
			</div>

			<Button
				type="submit"
				class="mt-2 w-full"
				tabindex="5"
				:disabled="processing"
				data-test="register-user-button"
			>
				<Spinner v-if="processing" />
				Criar conta
			</Button>
		</div>

		<div class="text-center text-sm text-muted-foreground">
			Já tem uma conta?
			<TextLink
				:href="login()"
				class="underline underline-offset-4"
				:tabindex="6"
			>
				Entrar
			</TextLink>
		</div>
	</Form>
</template>

<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';

import AuthLayout from '@/components/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineOptions({
	layout: AuthLayout,
});
</script>
