<script setup lang="ts">
import { computed } from 'vue';

import type { Direction } from '@/domain/Shared/types/common';

const props = defineProps<{
	direction: Direction;
}>();

const config = computed(() => {
	const map: Record<Direction, { label: string; class: string }> = {
		INFLOW: { label: 'Entrada', class: 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800' },
		OUTFLOW: { label: 'Saída', class: 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800' },
	};
	return map[props.direction];
});
</script>

<template>
	<Badge variant="outline" :class="config.class">
		{{ config.label }}
	</Badge>
</template>
