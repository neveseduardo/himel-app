<script setup lang="ts">
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import DataTable from '@/modules/finance/components/DataTable.vue';
import FilterBar from '@/modules/finance/components/FilterBar.vue';
import { useFinanceFilters } from '@/modules/finance/composables/useFinanceFilters';
import { usePagination } from '@/modules/finance/composables/usePagination';
import type { CreditCard, PaginationMeta } from '@/modules/finance/types/finance';
import type { BreadcrumbItem } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';

const props = defineProps<{
	creditCards: CreditCard[];
	meta: PaginationMeta;
	filters: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Cartões', href: '/finance/credit-cards' },
];

const columns = [
	{ key: 'name', label: 'Nome' },
	{ key: 'last_four_digits', label: 'Final' },
	{ key: 'card_type', label: 'Tipo' },
	{ key: 'closing_day', label: 'Fechamento' },
	{ key: 'due_day', label: 'Vencimento' },
	{ key: 'actions', label: '' },
];

const { filters, applyFilters, resetFilters } = useFinanceFilters(props.filters);
const { goToPage } = usePagination();

function deleteCreditCard(uid: string) {
	if (confirm('Tem certeza que deseja excluir este cartão?')) {
		router.delete(`/finance/credit-cards/${uid}`);
	}
}
</script>

<template>
	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="flex flex-col gap-6 p-6">
			<div class="flex items-center justify-between">
				<h1 class="text-2xl font-semibold">Cartões de Crédito</h1>
				<Link href="/finance/credit-cards/create">
					<Button size="sm"><Plus class="mr-2 size-4" /> Novo Cartão</Button>
				</Link>
			</div>

			<FilterBar v-model="filters.search" @search="applyFilters('/finance/credit-cards')" @reset="resetFilters('/finance/credit-cards')">
			</FilterBar>

			<DataTable :columns="columns" :data="creditCards as unknown as Record<string, unknown>[]">
				<template #cell-last_four_digits="{ row }">
					•••• {{ (row as unknown as CreditCard).last_four_digits }}
				</template>
				<template #cell-card_type="{ row }">
					<Badge variant="outline">{{ (row as unknown as CreditCard).card_type === 'PHYSICAL' ? 'Físico' : 'Virtual' }}</Badge>
				</template>
				<template #cell-closing_day="{ value }">
					Dia {{ value }}
				</template>
				<template #cell-due_day="{ value }">
					Dia {{ value }}
				</template>
				<template #cell-actions="{ row }">
					<div class="flex justify-end gap-2">
						<Link :href="`/finance/credit-cards/${(row as unknown as CreditCard).uid}/edit`">
							<Button variant="outline" size="sm">Editar</Button>
						</Link>
						<Button variant="destructive" size="sm" @click="deleteCreditCard((row as unknown as CreditCard).uid)">Excluir</Button>
					</div>
				</template>
			</DataTable>

			<div v-if="meta.last_page > 1" class="flex justify-center gap-2">
				<Button variant="outline" size="sm" :disabled="meta.current_page <= 1" @click="goToPage('/finance/credit-cards', meta.current_page - 1, filters)">Anterior</Button>
				<span class="flex items-center px-3 text-sm">{{ meta.current_page }} / {{ meta.last_page }}</span>
				<Button variant="outline" size="sm" :disabled="meta.current_page >= meta.last_page" @click="goToPage('/finance/credit-cards', meta.current_page + 1, filters)">Próxima</Button>
			</div>
		</div>
	</AppLayout>
</template>
