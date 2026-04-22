<script setup lang="ts">
import { Search, X } from 'lucide-vue-next';

const model = defineModel<string>({ default: '' });

defineEmits<{
	search: [];
	reset: [];
}>();
</script>

<template>
	<div class="flex items-center gap-3">
		<div class="relative flex-1">
			<Search class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
			<Input
				v-model="model"
				placeholder="Buscar..."
				class="pl-9"
				@keyup.enter="$emit('search')"
			/>
		</div>
		<slot />
		<div class="flex shrink-0 items-center gap-2">
			<Button variant="outline" size="sm" @click="$emit('search')">
				Buscar
			</Button>
			<Button variant="ghost" size="sm" @click="$emit('reset')">
				<X class="size-4" />
				Limpar
			</Button>
		</div>
	</div>
</template>
