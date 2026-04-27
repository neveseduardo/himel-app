<script setup lang="ts">
import { Donut } from '@unovis/ts';
import { VisDonut, VisSingleContainer, VisTooltip } from '@unovis/vue';
import { computed } from 'vue';

import type { PeriodSummary } from '@/domain/Period/types/period';
import { ChartContainer, type ChartConfig } from '@/domain/Shared/components/ui/chart';
import { formatCurrency } from '@/domain/Shared/services/format';

const props = defineProps<{
	summary: PeriodSummary;
}>();

const chartConfig = {
	fixed: { label: 'Despesas Fixas', color: 'hsl(12 76% 61%)' },
	card: { label: 'Parcelas de Cartão', color: 'hsl(173 58% 39%)' },
	manual: { label: 'Manuais', color: 'hsl(197 37% 24%)' },
	transfer: { label: 'Transferências', color: 'hsl(43 74% 66%)' },
} satisfies ChartConfig;

const configEntries = Object.entries(chartConfig);

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

const triggers = {
	[Donut.selectors.segment]: (d: Record<string, number>, i: number) => {
		const entry = configEntries[i];
		if (!entry) return '';
		const [, config] = entry;
		const value = Object.values(d)[0];
		return `<div class="rounded-lg border bg-background px-3 py-1.5 text-xs shadow-md"><span style="color: ${config.color}">●</span> ${config.label}: ${formatCurrency(value)}</div>`;
	},
};
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
			<VisTooltip :triggers="triggers" />
		</VisSingleContainer>
	</ChartContainer>
</template>
