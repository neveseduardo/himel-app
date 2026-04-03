<template>
	<Head title="Verificação de e-mail" />

	<div
		v-if="status === 'verification-link-sent'"
		class="mb-4 text-center text-sm font-medium text-green-600"
	>
		Um novo link de verificação foi enviado para o endereço de e-mail que
		você forneceu durante o cadastro.
	</div>

	<Form
		v-slot="{ processing }"
		v-bind="send.form()"
		class="space-y-6 text-center"
	>
		<Button :disabled="processing" variant="secondary">
			<Spinner v-if="processing" />
			Reenviar e-mail de verificação
		</Button>

		<TextLink :href="logout()" as="button" class="mx-auto block text-sm">
			Sair
		</TextLink>
	</Form>
</template>

<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';

import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineProps<{
    status?: string;
}>();

defineOptions({
	layout: AuthLayout,
});
</script>
