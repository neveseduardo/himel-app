<script setup lang="ts">
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import DataTable from '@/modules/finance/components/DataTable.vue';
import FilterBar from '@/modules/finance/components/FilterBar.vue';
import { useFinanceFilters } from '@/modules/finance/composables/useFinanceFilters';
import { usePagination } from '@/modules/finance/composables/usePagination';
import { formatCurrency } from '@/modules/finance/services/finance.services';
import type { CreditCardCharge, PaginationMeta } from '@/modules/finance/types/finance';
import type { BreadcrumbItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';

const props = defineProps<{
	charges: CreditCardCharge[];
	meta: PaginationMeta;
	filters: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Compras Cartão', href: '/finance/credit-card-charges' },
];

const columns = [
	{ key: 'description', label: 'Descrição' },
	{ key: 'total_amount', label: 'Valor Total' },
	{ key: 'installments', label: 'Parcelas' },
	{ key: 'credit_card', label: 'Cartão' },
	{ key: 'actions', label: '' },
];

const { filters, applyFilters, resetFilters } = useFinanceFilters(props.filters);
const { goToPage } = usePagination();
</script>

<template>
	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="flex flex-col gap-6 p-6">
			<div class="flex items-center justify-between">
				<h1 class="text-2xl font-semibold">Compras no Cartão</h1>
				<Link href="/finance/credit-card-charges/create">
					<Button size="sm"><Plus class="mr-2 size-4" /> Nova Compra</Button>
				</Link>
			</div>

			<FilterBar v-model="filters.search" @search="applyFilters('/finance/credit-card-charges')" @reset="resetFilters('/finance/credit-card-charges')">
			</FilterBar>

			<DataTable :columns="columns" :data="charges as unknown as Record<string, unknown>[]">
				<template #cell-total_amount="{ row }">
					{{ formatCurrency((row as unknown as CreditCardCharge).total_amount) }}
				</template>
				<template #cell-installments="{ row }">
					{{ (row as unknown as CreditCardCharge).installments }}x
				</template>
				<template #cell-credit_card="{ row }">
					{{ (row as unknown as CreditCardCharge).credit_card?.name ?? '—' }}
				</template>
				<template #cell-actions="{ row }">
					<div class="flex justify-end">
						<Link :href="`/finance/credit-card-charges/${(row as unknown as CreditCardCharge).uid}`">
							<Button variant="outline" size="sm">Detalhes</Button>
						</Link>
					</div>
				</template>
			</DataTable>

			<div v-if="meta.last_page > 1" class="flex justify-center gap-2">
				<Button variant="outline" size="sm" :disabled="meta.current_page <= 1" @click="goToPage('/finance/credit-card-charges', meta.current_page - 1, filters)">Anterior</Button>
				<span class="flex items-center px-3 text-sm">{{ meta.current_page }} / {{ meta.last_page }}</span>
				<Button variant="outline" size="sm" :disabled="meta.current_page >= meta.last_page" @click="goToPage('/finance/credit-card-charges', meta.current_page + 1, filters)">Próxima</Button>
			</div>
		</div>
	</AppLayout>
</template>
