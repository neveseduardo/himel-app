<template>
	<Dialog v-model:open="model">
		<DialogContent>
			<DialogHeader>
				<DialogTitle>{{ title }}</DialogTitle>
				<DialogDescription v-if="description">
					{{ description }}
				</DialogDescription>
			</DialogHeader>

			<slot />

			<DialogFooter>
				<Button type="button" variant="outline" @click="closeDialog">
					Cancelar
				</Button>
				<Button
					type="button"
					variant="destructive"
					:disabled="isDeleting"
					@click="handleDelete"
				>
					{{ isDeleting ? "Excluindo..." : "Excluir" }}
				</Button>
			</DialogFooter>
		</DialogContent>
	</Dialog>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

import type { RouteDefinition } from '@/wayfinder';

const props = defineProps<{
    title?: string;
    description?: string;
    deleteRoute?: RouteDefinition<'delete'>;
    confirmMessage?: string;
}>();

const model = defineModel<boolean>('open', { default: false });
const isDeleting = ref(false);

function openDialog() {
	model.value = true;
}

function closeDialog() {
	model.value = false;
	isDeleting.value = false;
}

async function handleDelete() {
	if (isDeleting.value) return;

	isDeleting.value = true;

	if (!props.deleteRoute || typeof props.deleteRoute.url !== 'string') {
		isDeleting.value = false;
		return;
	}

	const url = props.deleteRoute.url;

	router.delete(url, {
		onSuccess: () => {
			closeDialog();
			toast.success('Excluído com sucesso!');
		},
		onError: (errors) => {
			isDeleting.value = false;
			toast.error(Object.values(errors)[0]);
		},
	});
}

defineExpose({
	openDialog,
	closeDialog,
});
</script>
