<script setup lang="ts">
import { VisDonut, VisSingleContainer, VisTooltip } from '@unovis/vue';
import { computed } from 'vue';

import type { PeriodSummary } from '@/domain/Period/types/period';
import { ChartContainer, type ChartConfig } from '@/domain/Shared/components/ui/chart';

const props = defineProps<{
	summary: PeriodSummary;
}>();

const chartConfig = {
	fixed: { label: 'Despesas Fixas', color: 'hsl(var(--chart-1))' },
	card: { label: 'Parcelas de Cartão', color: 'hsl(var(--chart-2))' },
	manual: { label: 'Manuais', color: 'hsl(var(--chart-3))' },
	transfer: { label: 'Transferências', color: 'hsl(var(--chart-4))' },
} satisfies ChartConfig;

const isEmpty = computed(() => {
	return (
		(props.summary.total_fixed_expenses ?? 0) === 0 &&
		(props.summary.total_credit_card_installments ?? 0) === 0 &&
		(props.summary.total_manual ?? 0) === 0 &&
		(props.summary.total_transfer ?? 0) === 0
	);
});

const data = computed(() => [
	{ fixed: props.summary.total_fixed_expenses ?? 0 },
	{ card: props.summary.total_credit_card_installments ?? 0 },
	{ manual: props.summary.total_manual ?? 0 },
	{ transfer: props.summary.total_transfer ?? 0 },
]);

const colors = computed(() =>
	Object.values(chartConfig).map(c => c.color)
);
</script>

<template>
	<div v-if="isEmpty" class="flex h-[300px] items-center justify-center">
		<p class="text-sm text-muted-foreground">
			Sem saídas neste período
		</p>
	</div>
	<ChartContainer v-else :config="chartConfig" class="h-[300px]">
		<VisSingleContainer :data="data">
			<VisDonut
				:value="(d: Record<string, number>) => Object.values(d)[0]"
				:pad-angle="0.01"
				:arc-width="60"
				:color="colors"
			/>
			<VisTooltip />
		</VisSingleContainer>
	</ChartContainer>
</template>
