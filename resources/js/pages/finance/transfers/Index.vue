<script setup lang="ts">
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import DataTable from '@/modules/finance/components/DataTable.vue';
import FilterBar from '@/modules/finance/components/FilterBar.vue';
import { useFinanceFilters } from '@/modules/finance/composables/useFinanceFilters';
import { usePagination } from '@/modules/finance/composables/usePagination';
import { formatCurrency, formatDate } from '@/modules/finance/services/finance.services';
import type { PaginationMeta, Transfer } from '@/modules/finance/types/finance';
import type { BreadcrumbItem } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';

const props = defineProps<{
	transfers: Transfer[];
	meta: PaginationMeta;
	filters: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Transferências', href: '/finance/transfers' },
];

const columns = [
	{ key: 'from_account', label: 'Origem' },
	{ key: 'to_account', label: 'Destino' },
	{ key: 'amount', label: 'Valor' },
	{ key: 'occurred_at', label: 'Data' },
	{ key: 'actions', label: '' },
];

const { filters, applyFilters, resetFilters } = useFinanceFilters(props.filters);
const { goToPage } = usePagination();

function deleteTransfer(uid: string) {
	if (confirm('Tem certeza que deseja excluir esta transferência?')) {
		router.delete(`/finance/transfers/${uid}`);
	}
}
</script>

<template>
	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="flex flex-col gap-6 p-6">
			<div class="flex items-center justify-between">
				<h1 class="text-2xl font-semibold">Transferências</h1>
				<Link href="/finance/transfers/create">
					<Button size="sm"><Plus class="mr-2 size-4" /> Nova Transferência</Button>
				</Link>
			</div>

			<FilterBar v-model="filters.search" @search="applyFilters('/finance/transfers')" @reset="resetFilters('/finance/transfers')">
			</FilterBar>

			<DataTable :columns="columns" :data="transfers as unknown as Record<string, unknown>[]">
				<template #cell-from_account="{ row }">
					{{ (row as unknown as Transfer).from_account?.name ?? '—' }}
				</template>
				<template #cell-to_account="{ row }">
					{{ (row as unknown as Transfer).to_account?.name ?? '—' }}
				</template>
				<template #cell-amount="{ row }">
					{{ formatCurrency((row as unknown as Transfer).amount) }}
				</template>
				<template #cell-occurred_at="{ row }">
					{{ formatDate((row as unknown as Transfer).occurred_at) }}
				</template>
				<template #cell-actions="{ row }">
					<div class="flex justify-end gap-1">
						<Button variant="destructive" size="sm" @click="deleteTransfer((row as unknown as Transfer).uid)">Excluir</Button>
					</div>
				</template>
			</DataTable>

			<div v-if="meta.last_page > 1" class="flex justify-center gap-2">
				<Button variant="outline" size="sm" :disabled="meta.current_page <= 1" @click="goToPage('/finance/transfers', meta.current_page - 1, filters)">Anterior</Button>
				<span class="flex items-center px-3 text-sm">{{ meta.current_page }} / {{ meta.last_page }}</span>
				<Button variant="outline" size="sm" :disabled="meta.current_page >= meta.last_page" @click="goToPage('/finance/transfers', meta.current_page + 1, filters)">Próxima</Button>
			</div>
		</div>
	</AppLayout>
</template>
