<script setup lang="ts">
import { VisAxis, VisStackedBar, VisTooltip, VisXYContainer } from '@unovis/vue';
import { computed } from 'vue';

import type { CategoryBreakdownItem } from '@/domain/Dashboard/types/dashboard';
import { ChartContainer, type ChartConfig } from '@/domain/Shared/components/ui/chart';
import { formatCurrency } from '@/domain/Shared/services/format';

const props = defineProps<{
	categoryBreakdown: CategoryBreakdownItem[];
}>();

const chartConfig = {
	total: { label: 'Total', color: 'hsl(var(--chart-2))' },
} satisfies ChartConfig;

interface BarData {
	name: string;
	total: number;
}

const data = computed<BarData[]>(() =>
	props.categoryBreakdown.map(item => ({
		name: item.category_name,
		total: item.total,
	}))
);

const isEmpty = computed(() => props.categoryBreakdown.length === 0);
</script>

<template>
	<div v-if="isEmpty" class="flex h-[300px] items-center justify-center">
		<p class="text-sm text-muted-foreground">
			Sem dados
		</p>
	</div>
	<ChartContainer v-else :config="chartConfig" class="h-[300px]">
		<VisXYContainer :data="data">
			<VisStackedBar
				:x="(_d: BarData, i: number) => i"
				:y="[(d: BarData) => d.total]"
				:color="[chartConfig.total.color]"
				:rounded-corners="4"
				:bar-padding="0.3"
				orientation="horizontal"
			/>
			<VisAxis type="x" :tick-format="(v: number) => formatCurrency(v)" />
			<VisAxis type="y" :tick-format="(i: number) => data[i]?.name ?? ''" />
			<VisTooltip />
		</VisXYContainer>
	</ChartContainer>
</template>
