<script setup lang="ts">
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import DataTable from '@/modules/finance/components/DataTable.vue';
import FilterBar from '@/modules/finance/components/FilterBar.vue';
import { useFinanceFilters } from '@/modules/finance/composables/useFinanceFilters';
import { usePagination } from '@/modules/finance/composables/usePagination';
import { formatCurrency } from '@/modules/finance/services/finance.services';
import type { FixedExpense, PaginationMeta } from '@/modules/finance/types/finance';
import type { BreadcrumbItem } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';

const props = defineProps<{
	fixedExpenses: FixedExpense[];
	meta: PaginationMeta;
	filters: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Despesas Fixas', href: '/finance/fixed-expenses' },
];

const columns = [
	{ key: 'description', label: 'Descrição' },
	{ key: 'amount', label: 'Valor' },
	{ key: 'due_day', label: 'Dia Venc.' },
	{ key: 'active', label: 'Status' },
	{ key: 'actions', label: '' },
];

const { filters, applyFilters, resetFilters } = useFinanceFilters(props.filters);
const { goToPage } = usePagination();

function deleteFixedExpense(uid: string) {
	if (confirm('Tem certeza que deseja excluir esta despesa fixa?')) {
		router.delete(`/finance/fixed-expenses/${uid}`);
	}
}
</script>

<template>
	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="flex flex-col gap-6 p-6">
			<div class="flex items-center justify-between">
				<h1 class="text-2xl font-semibold">Despesas Fixas</h1>
				<Link href="/finance/fixed-expenses/create">
					<Button size="sm"><Plus class="mr-2 size-4" /> Nova Despesa Fixa</Button>
				</Link>
			</div>

			<FilterBar v-model="filters.search" @search="applyFilters('/finance/fixed-expenses')" @reset="resetFilters('/finance/fixed-expenses')">
			</FilterBar>

			<DataTable :columns="columns" :data="fixedExpenses as unknown as Record<string, unknown>[]">
				<template #cell-amount="{ row }">
					{{ formatCurrency((row as unknown as FixedExpense).amount) }}
				</template>
				<template #cell-active="{ row }">
					<Badge :variant="(row as unknown as FixedExpense).active ? 'default' : 'secondary'">
						{{ (row as unknown as FixedExpense).active ? 'Ativa' : 'Inativa' }}
					</Badge>
				</template>
				<template #cell-actions="{ row }">
					<div class="flex justify-end gap-2">
						<Link :href="`/finance/fixed-expenses/${(row as unknown as FixedExpense).uid}/edit`">
							<Button variant="outline" size="sm">Editar</Button>
						</Link>
						<Button variant="destructive" size="sm" @click="deleteFixedExpense((row as unknown as FixedExpense).uid)">Excluir</Button>
					</div>
				</template>
			</DataTable>

			<div v-if="meta.last_page > 1" class="flex justify-center gap-2">
				<Button variant="outline" size="sm" :disabled="meta.current_page <= 1" @click="goToPage('/finance/fixed-expenses', meta.current_page - 1, filters)">Anterior</Button>
				<span class="flex items-center px-3 text-sm">{{ meta.current_page }} / {{ meta.last_page }}</span>
				<Button variant="outline" size="sm" :disabled="meta.current_page >= meta.last_page" @click="goToPage('/finance/fixed-expenses', meta.current_page + 1, filters)">Próxima</Button>
			</div>
		</div>
	</AppLayout>
</template>
