<script setup lang="ts">
import { computed } from 'vue';

import { Badge } from '@/components/ui/badge';

import type { TransactionStatus } from '../types/finance';

const props = defineProps<{
	status: TransactionStatus;
}>();

const config = computed(() => {
	const map: Record<TransactionStatus, { label: string; class: string }> = {
		PENDING: { label: 'Pendente', class: 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-400 dark:border-yellow-800' },
		PAID: { label: 'Pago', class: 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800' },
		OVERDUE: { label: 'Atrasado', class: 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800' },
	};
	return map[props.status];
});
</script>

<template>
	<Badge variant="outline" :class="config.class">
		{{ config.label }}
	</Badge>
</template>
