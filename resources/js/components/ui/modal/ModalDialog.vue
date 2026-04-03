<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
}>();

const showDialog = ref(false);

function openDialog() {
	showDialog.value = true;
}

function closeDialog() {
	showDialog.value = false;
}

defineExpose({
	openDialog,
	closeDialog,
});
</script>

<template>
	<Dialog v-model:open="showDialog">
		<DialogContent class="pointer-events-auto">
			<DialogHeader>
				<DialogTitle>{{ title }}</DialogTitle>
				<DialogDescription v-if="description">
					{{ description }}
				</DialogDescription>
			</DialogHeader>

			<slot />
		</DialogContent>
	</Dialog>
</template>
