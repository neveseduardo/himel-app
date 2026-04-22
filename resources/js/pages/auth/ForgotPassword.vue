<template>
	<Head title="Esqueci minha senha">
		<meta name="description" content="Recupere o acesso à sua conta informando seu e-mail.">
	</Head>

	<div
		v-if="status"
		class="mb-4 text-center text-sm font-medium text-green-600"
	>
		{{ status }}
	</div>

	<div class="space-y-6">
		<Form v-slot="{ errors, processing }" v-bind="email.form()">
			<div class="grid gap-2">
				<Label for="email">Endereço de e-mail</Label>
				<Input
					id="email"
					type="email"
					name="email"
					autocomplete="off"
					autofocus
					placeholder="email@example.com"
				/>
				<InputError :message="errors.email" />
			</div>

			<div class="my-6 flex items-center justify-start">
				<Button
					class="w-full"
					:disabled="processing"
					data-test="email-password-reset-link-button"
				>
					<Spinner v-if="processing" />
					Enviar link de redefinição de senha
				</Button>
			</div>
		</Form>

		<div class="space-x-1 text-center text-sm text-muted-foreground">
			<span>Or, return to</span>
			<TextLink :href="login()">
				log in
			</TextLink>
		</div>
	</div>
</template>

<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';

import { login } from '@/routes';
import { email } from '@/routes/password';

defineProps<{
    status?: string;
}>();
</script>
