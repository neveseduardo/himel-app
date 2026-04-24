<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';

import PeriodSelector from '@/domain/Dashboard/components/PeriodSelector.vue';
import type { DashboardProps } from '@/domain/Dashboard/types/dashboard';
import { formatCurrency } from '@/domain/Shared/services/format';

defineProps<DashboardProps>();

function handlePeriodChange(uid: string) {
	router.get('/dashboard', { period: uid }, { preserveState: false });
}
</script>

<template>
	<div class="flex flex-col gap-6 p-6">
		<Head title="Dashboard">
			<meta name="description" content="Painel principal da sua gestão financeira.">
		</Head>

		<PageHeader title="Dashboard">
			<template #actions>
				<PeriodSelector
					v-if="periods.length > 0"
					:periods="periods"
					:selected-uid="period?.uid ?? null"
					@update:selected-uid="handlePeriodChange"
				/>
			</template>
		</PageHeader>

		<!-- Empty state -->
		<template v-if="!period">
			<Card>
				<CardContent class="py-12 text-center">
					<p class="text-muted-foreground">
						Nenhum período encontrado. Crie um período para visualizar o dashboard.
					</p>
				</CardContent>
			</Card>
		</template>

		<template v-else>
			<!-- Summary cards -->
			<div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
				<Card>
					<CardHeader class="pb-2">
						<CardTitle class="text-sm font-medium text-muted-foreground">
							Entradas
						</CardTitle>
					</CardHeader>
					<CardContent>
						<p class="text-2xl font-bold text-green-600 dark:text-green-400">
							{{ formatCurrency(summary.total_inflow) }}
						</p>
					</CardContent>
				</Card>

				<Card>
					<CardHeader class="pb-2">
						<CardTitle class="text-sm font-medium text-muted-foreground">
							Saídas
						</CardTitle>
					</CardHeader>
					<CardContent>
						<p class="text-2xl font-bold text-red-600 dark:text-red-400">
							{{ formatCurrency(summary.total_outflow) }}
						</p>
					</CardContent>
				</Card>

				<Card>
					<CardHeader class="pb-2">
						<CardTitle class="text-sm font-medium text-muted-foreground">
							Saldo
						</CardTitle>
					</CardHeader>
					<CardContent>
						<p class="text-2xl font-bold" :class="summary.balance >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
							{{ formatCurrency(summary.balance) }}
						</p>
					</CardContent>
				</Card>

				<Card>
					<CardHeader class="pb-2">
						<CardTitle class="text-sm font-medium text-muted-foreground">
							Total Cartões
						</CardTitle>
					</CardHeader>
					<CardContent>
						<p class="text-2xl font-bold text-red-600 dark:text-red-400">
							{{ formatCurrency(cardBreakdown.grand_total) }}
						</p>
					</CardContent>
				</Card>
			</div>

			<!-- Charts section -->
			<div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
				<Card data-testid="chart-outflow-composition">
					<CardHeader>
						<CardTitle>Composição de Saídas</CardTitle>
					</CardHeader>
					<CardContent>
						<p class="text-sm text-muted-foreground">
							Gráfico em breve
						</p>
					</CardContent>
				</Card>

				<Card data-testid="chart-inflow-vs-outflow">
					<CardHeader>
						<CardTitle>Entradas vs Saídas</CardTitle>
					</CardHeader>
					<CardContent>
						<p class="text-sm text-muted-foreground">
							Gráfico em breve
						</p>
					</CardContent>
				</Card>

				<Card data-testid="chart-card-breakdown">
					<CardHeader>
						<CardTitle>Breakdown por Cartão</CardTitle>
					</CardHeader>
					<CardContent>
						<p class="text-sm text-muted-foreground">
							Gráfico em breve
						</p>
					</CardContent>
				</Card>

				<Card data-testid="chart-status">
					<CardHeader>
						<CardTitle>Transações por Status</CardTitle>
					</CardHeader>
					<CardContent>
						<p class="text-sm text-muted-foreground">
							Gráfico em breve
						</p>
					</CardContent>
				</Card>

				<Card data-testid="chart-category-breakdown">
					<CardHeader>
						<CardTitle>Gastos por Categoria</CardTitle>
					</CardHeader>
					<CardContent>
						<p class="text-sm text-muted-foreground">
							Gráfico em breve
						</p>
					</CardContent>
				</Card>
			</div>
		</template>
	</div>
</template>
