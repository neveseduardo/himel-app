<script setup lang="ts">
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import DataTable from '@/modules/finance/components/DataTable.vue';
import DirectionBadge from '@/modules/finance/components/DirectionBadge.vue';
import FilterBar from '@/modules/finance/components/FilterBar.vue';
import StatusBadge from '@/modules/finance/components/StatusBadge.vue';
import { useFinanceFilters } from '@/modules/finance/composables/useFinanceFilters';
import { usePagination } from '@/modules/finance/composables/usePagination';
import { formatCurrency, formatDate } from '@/modules/finance/services/finance.services';
import type { PaginationMeta, Transaction } from '@/modules/finance/types/finance';
import type { BreadcrumbItem } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';

const props = defineProps<{
	transactions: Transaction[];
	meta: PaginationMeta;
	filters: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Transações', href: '/finance/transactions' },
];

const columns = [
	{ key: 'description', label: 'Descrição' },
	{ key: 'amount', label: 'Valor' },
	{ key: 'direction', label: 'Direção' },
	{ key: 'status', label: 'Status' },
	{ key: 'occurred_at', label: 'Data' },
	{ key: 'actions', label: '' },
];

const { filters, applyFilters, resetFilters } = useFinanceFilters(props.filters);
const { goToPage } = usePagination();

function deleteTransaction(uid: string) {
	if (confirm('Tem certeza que deseja excluir esta transação?')) {
		router.delete(`/finance/transactions/${uid}`);
	}
}
</script>

<template>
	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="flex flex-col gap-6 p-6">
			<div class="flex items-center justify-between">
				<h1 class="text-2xl font-semibold">Transações</h1>
				<Link href="/finance/transactions/create">
					<Button size="sm"><Plus class="mr-2 size-4" /> Nova Transação</Button>
				</Link>
			</div>

			<FilterBar v-model="filters.search" @search="applyFilters('/finance/transactions')" @reset="resetFilters('/finance/transactions')">
			</FilterBar>

			<DataTable :columns="columns" :data="transactions as unknown as Record<string, unknown>[]">
				<template #cell-description="{ row }">
					{{ (row as unknown as Transaction).description || '—' }}
				</template>
				<template #cell-amount="{ row }">
					{{ formatCurrency((row as unknown as Transaction).amount) }}
				</template>
				<template #cell-direction="{ row }">
					<DirectionBadge :direction="(row as unknown as Transaction).direction" />
				</template>
				<template #cell-status="{ row }">
					<StatusBadge :status="(row as unknown as Transaction).status" />
				</template>
				<template #cell-occurred_at="{ row }">
					{{ formatDate((row as unknown as Transaction).occurred_at) }}
				</template>
				<template #cell-actions="{ row }">
					<div class="flex justify-end gap-2">
						<Link :href="`/finance/transactions/${(row as unknown as Transaction).uid}/edit`">
							<Button variant="outline" size="sm">Editar</Button>
						</Link>
						<Button variant="destructive" size="sm" @click="deleteTransaction((row as unknown as Transaction).uid)">Excluir</Button>
					</div>
				</template>
			</DataTable>

			<div v-if="meta.last_page > 1" class="flex justify-center gap-2">
				<Button variant="outline" size="sm" :disabled="meta.current_page <= 1" @click="goToPage('/finance/transactions', meta.current_page - 1, filters)">Anterior</Button>
				<span class="flex items-center px-3 text-sm">{{ meta.current_page }} / {{ meta.last_page }}</span>
				<Button variant="outline" size="sm" :disabled="meta.current_page >= meta.last_page" @click="goToPage('/finance/transactions', meta.current_page + 1, filters)">Próxima</Button>
			</div>
		</div>
	</AppLayout>
</template>
