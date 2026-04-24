<script setup lang="ts">
import { VisDonut, VisSingleContainer, VisTooltip } from '@unovis/vue';
import { computed } from 'vue';

import type { StatusCounts } from '@/domain/Dashboard/types/dashboard';
import { ChartContainer, ChartTooltipContent, componentToString, type ChartConfig } from '@/domain/Shared/components/ui/chart';

const props = defineProps<{
	statusCounts: StatusCounts;
}>();

const chartConfig = {
	pending: { label: 'Pendente', color: 'hsl(48 96% 53%)' },
	paid: { label: 'Pago', color: 'hsl(142 76% 36%)' },
	overdue: { label: 'Vencido', color: 'hsl(0 84% 60%)' },
} satisfies ChartConfig;

const isEmpty = computed(() => {
	return props.statusCounts.pending === 0 &&
		props.statusCounts.paid === 0 &&
		props.statusCounts.overdue === 0;
});

const totalCount = computed(() =>
	props.statusCounts.pending + props.statusCounts.paid + props.statusCounts.overdue
);

const data = computed(() => [
	{ pending: props.statusCounts.pending },
	{ paid: props.statusCounts.paid },
	{ overdue: props.statusCounts.overdue },
]);

const colors = computed(() =>
	Object.values(chartConfig).map(c => c.color)
);
</script>

<template>
	<div v-if="isEmpty" class="flex h-[300px] items-center justify-center">
		<p class="text-sm text-muted-foreground">
			Sem transações
		</p>
	</div>
	<ChartContainer v-else :config="chartConfig" class="relative h-[300px]">
		<VisSingleContainer :data="data">
			<VisDonut
				:value="(d: Record<string, number>) => Object.values(d)[0]"
				:pad-angle="0.01"
				:arc-width="60"
				:color="colors"
			/>
			<VisTooltip
				:attributes="{ [VisTooltip.selectors.tooltip]: { class: '' } }"
				:content="componentToString(chartConfig, ChartTooltipContent)"
			/>
		</VisSingleContainer>
		<div class="pointer-events-none absolute inset-0 flex items-center justify-center">
			<div class="text-center">
				<p class="text-3xl font-bold">
					{{ totalCount }}
				</p>
				<p class="text-xs text-muted-foreground">
					transações
				</p>
			</div>
		</div>
	</ChartContainer>
</template>
