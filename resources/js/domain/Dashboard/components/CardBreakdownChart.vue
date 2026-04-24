<script setup lang="ts">
import { VisAxis, VisStackedBar, VisTooltip, VisXYContainer } from '@unovis/vue';
import { computed } from 'vue';

import type { PeriodCardBreakdown } from '@/domain/Period/types/period';
import { ChartContainer, type ChartConfig } from '@/domain/Shared/components/ui/chart';
import { formatCurrency } from '@/domain/Shared/services/format';

const props = defineProps<{
	cardBreakdown: PeriodCardBreakdown;
}>();

const chartConfig = {
	total: { label: 'Total', color: 'hsl(var(--chart-1))' },
} satisfies ChartConfig;

interface BarData {
	name: string;
	total: number;
}

const data = computed<BarData[]>(() =>
	props.cardBreakdown.cards.map(card => ({
		name: card.credit_card_name,
		total: card.total,
	}))
);

const isEmpty = computed(() => props.cardBreakdown.cards.length === 0);
</script>

<template>
	<div v-if="isEmpty" class="flex h-[300px] items-center justify-center">
		<p class="text-sm text-muted-foreground">
			Sem dados de cartão
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
