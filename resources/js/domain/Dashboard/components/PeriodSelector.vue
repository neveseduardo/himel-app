<script setup lang="ts">
import type { Period } from '@/domain/Period/types/period';

const props = defineProps<{
	periods: Period[];
	selectedUid: string | null;
}>();

const emit = defineEmits<{
	'update:selectedUid': [uid: string];
}>();

const monthNames = [
	'', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
	'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro',
];

function formatPeriodLabel(month: number, year: number): string {
	return `${monthNames[month]} ${year}`;
}

const selectedLabel = computed(() => {
	const period = props.periods.find((p) => p.uid === props.selectedUid);
	if (period) return formatPeriodLabel(period.month, period.year);
	return 'Selecione um período';
});

function handleChange(uid: string) {
	emit('update:selectedUid', uid);
}
</script>

<template>
	<Select :model-value="selectedUid ?? undefined" @update:model-value="handleChange">
		<SelectTrigger class="w-[200px]">
			<SelectValue :placeholder="selectedLabel" />
		</SelectTrigger>
		<SelectContent>
			<SelectItem v-for="period in periods" :key="period.uid" :value="period.uid">
				{{ formatPeriodLabel(period.month, period.year) }}
			</SelectItem>
		</SelectContent>
	</Select>
</template>
