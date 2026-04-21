<script setup lang="ts">
withDefaults(
	defineProps<{
		title?: string;
		description?: string;
		loading?: boolean;
	}>(),
	{
		title: 'Tem certeza?',
		description: 'Esta ação não pode ser desfeita.',
		loading: false,
	}
);

const emit = defineEmits<{
	confirm: [];
	cancel: [];
}>();

const isOpen = ref(false);

function handleConfirm() {
	emit('confirm');
}

function handleCancel() {
	isOpen.value = false;
	emit('cancel');
}
</script>

<template>
	<Popover v-model:open="isOpen">
		<PopoverTrigger as-child>
			<slot name="trigger" />
		</PopoverTrigger>
		<PopoverContent class="w-64">
			<div class="flex flex-col gap-3">
				<div class="flex flex-col gap-1">
					<p class="text-sm font-semibold">
						{{ title }}
					</p>
					<p class="text-muted-foreground text-xs">
						{{ description }}
					</p>
				</div>
				<div class="flex justify-end gap-2">
					<Button variant="outline" size="sm" @click="handleCancel">
						Cancelar
					</Button>
					<Button
						variant="destructive"
						size="sm"
						:disabled="loading"
						@click="handleConfirm"
					>
						{{ loading ? 'Excluindo...' : 'Excluir' }}
					</Button>
				</div>
			</div>
		</PopoverContent>
	</Popover>
</template>
