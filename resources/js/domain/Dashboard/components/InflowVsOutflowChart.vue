<script setup lang="ts">
import { VisAxis, VisGroupedBar, VisTooltip, VisXYContainer } from '@unovis/vue';
import { computed } from 'vue';

import type { PeriodSummary } from '@/domain/Period/types/period';
import { ChartContainer, ChartTooltipContent, componentToString, type ChartConfig } from '@/domain/Shared/components/ui/chart';
import { formatCurrency } from '@/domain/Shared/services/format';

const props = defineProps<{
	summary: PeriodSummary;
}>();

const chartConfig = {
	entradas: { label: 'Entradas', color: 'hsl(142 76% 36%)' },
	saidas: { label: 'Saídas', color: 'hsl(0 84% 60%)' },
} satisfies ChartConfig;

interface BarData {
	entradas: number;
	saidas: number;
}

const data = computed<BarData[]>(() => [
	{
		entradas: props.summary.total_inflow,
		saidas: props.summary.total_outflow,
	},
]);

const colors = computed(() =>
	Object.values(chartConfig).map(c => c.color)
);
</script>

<template>
	<ChartContainer :config="chartConfig" class="h-[300px]">
		<VisXYContainer :data="data">
			<VisGroupedBar
				:x="(_d: BarData, i: number) => i"
				:y="[(d: BarData) => d.entradas, (d: BarData) => d.saidas]"
				:color="colors"
				:rounded-corners="4"
				:bar-padding="0.3"
				:group-padding="0.2"
			/>
			<VisAxis type="x" :tick-format="() => 'Período'" />
			<VisAxis type="y" :tick-format="(v: number) => formatCurrency(v)" />
			<VisTooltip
				:attributes="{ [VisTooltip.selectors.tooltip]: { class: '' } }"
				:content="componentToString(chartConfig, ChartTooltipContent)"
			/>
		</VisXYContainer>
	</ChartContainer>
</template>
