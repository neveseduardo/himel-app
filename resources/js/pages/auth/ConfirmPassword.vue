<template>
	<Head title="Confirmar senha">
		<meta name="description" content="Confirme sua senha para continuar.">
	</Head>

	<Form
		v-slot="{ errors, processing }"
		v-bind="store.form()"
		reset-on-success
	>
		<div class="space-y-6">
			<div class="grid gap-2">
				<Label html-for="password">Senha</Label>
				<PasswordInput
					id="password"
					name="password"
					class="mt-1 block w-full"
					required
					autocomplete="current-password"
					autofocus
				/>

				<InputError :message="errors.password" />
			</div>

			<div class="flex items-center">
				<Button
					class="w-full"
					:disabled="processing"
					data-test="confirm-password-button"
				>
					<Spinner v-if="processing" />
					Confirmar senha
				</Button>
			</div>
		</div>
	</Form>
</template>

<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';

import { store } from '@/routes/password/confirm';
</script>
