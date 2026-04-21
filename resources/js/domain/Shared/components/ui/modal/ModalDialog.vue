<script setup lang="ts">
const props = defineProps<{
	title: string;
	description?: string;
	subtitle?: string;
}>();

const emit = defineEmits<{
	'update:open': [value: boolean];
}>();

const displaySubtitle = computed(() => props.subtitle ?? props.description);

const showDialog = ref(false);

watch(showDialog, (value) => {
	emit('update:open', value);
});

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
				<DialogDescription v-if="displaySubtitle">
					{{ displaySubtitle }}
				</DialogDescription>
			</DialogHeader>

			<slot />
		</DialogContent>
	</Dialog>
</template>
