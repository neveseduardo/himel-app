<template>
	<Head title="Redefinir senha" />

	<Form
		v-slot="{ errors, processing }"
		v-bind="update.form()"
		:transform="(data) => ({ ...data, token, email })"
		:reset-on-success="['password', 'password_confirmation']"
	>
		<div class="grid gap-6">
			<div class="grid gap-2">
				<Label for="email">E-mail</Label>
				<Input
					id="email"
					v-model="inputEmail"
					type="email"
					name="email"
					autocomplete="email"
					class="mt-1 block w-full"
					readonly
				/>
				<InputError :message="errors.email" class="mt-2" />
			</div>

			<div class="grid gap-2">
				<Label for="password">Senha</Label>
				<PasswordInput
					id="password"
					name="password"
					autocomplete="new-password"
					class="mt-1 block w-full"
					autofocus
					placeholder="Senha"
				/>
				<InputError :message="errors.password" />
			</div>

			<div class="grid gap-2">
				<Label for="password_confirmation"> Confirmar senha </Label>
				<PasswordInput
					id="password_confirmation"
					name="password_confirmation"
					autocomplete="new-password"
					class="mt-1 block w-full"
					placeholder="Confirmar senha"
				/>
				<InputError :message="errors.password_confirmation" />
			</div>

			<Button
				type="submit"
				class="mt-4 w-full"
				:disabled="processing"
				data-test="reset-password-button"
			>
				<Spinner v-if="processing" />
				Redefinir senha
			</Button>
		</div>
	</Form>
</template>

<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';

import { update } from '@/routes/password';

import AuthLayout from '@/layouts/AuthLayout.vue';

const props = defineProps<{
    token: string;
    email: string;
}>();

const inputEmail = ref(props.email);

defineOptions({
	layout: AuthLayout,
});
</script>
