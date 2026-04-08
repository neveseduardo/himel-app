<script setup lang="ts">
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import DataTable from '@/modules/finance/components/DataTable.vue';
import FilterBar from '@/modules/finance/components/FilterBar.vue';
import { useFinanceFilters } from '@/modules/finance/composables/useFinanceFilters';
import { usePagination } from '@/modules/finance/composables/usePagination';
import { formatCurrency } from '@/modules/finance/services/finance.services';
import type { Account, PaginationMeta } from '@/modules/finance/types/finance';
import type { BreadcrumbItem } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';

const props = defineProps<{
	accounts: Account[];
	meta: PaginationMeta;
	filters: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Contas', href: '/finance/accounts' },
];

const columns = [
	{ key: 'name', label: 'Nome' },
	{ key: 'type', label: 'Tipo' },
	{ key: 'balance', label: 'Saldo' },
	{ key: 'actions', label: '' },
];

const { filters, applyFilters, resetFilters } = useFinanceFilters(props.filters);
const { goToPage } = usePagination();

function deleteAccount(uid: string) {
	if (confirm('Tem certeza que deseja excluir esta conta?')) {
		router.delete(`/finance/accounts/${uid}`);
	}
}
</script>

<template>
	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="flex flex-col gap-6 p-6">
			<div class="flex items-center justify-between">
				<h1 class="text-2xl font-semibold">Contas</h1>
				<Link href="/finance/accounts/create">
					<Button size="sm"><Plus class="mr-2 size-4" /> Nova Conta</Button>
				</Link>
			</div>

			<FilterBar v-model="filters.search" @search="applyFilters('/finance/accounts')" @reset="resetFilters('/finance/accounts')">
			</FilterBar>

			<DataTable :columns="columns" :data="accounts as unknown as Record<string, unknown>[]">
				<template #cell-balance="{ row }">
					{{ formatCurrency((row as unknown as Account).balance) }}
				</template>
				<template #cell-actions="{ row }">
					<div class="flex justify-end gap-2">
						<Link :href="`/finance/accounts/${(row as unknown as Account).uid}/edit`">
							<Button variant="outline" size="sm">Editar</Button>
						</Link>
						<Button variant="destructive" size="sm" @click="deleteAccount((row as unknown as Account).uid)">Excluir</Button>
					</div>
				</template>
			</DataTable>

			<div v-if="meta.last_page > 1" class="flex justify-center gap-2">
				<Button variant="outline" size="sm" :disabled="meta.current_page <= 1" @click="goToPage('/finance/accounts', meta.current_page - 1, filters)">Anterior</Button>
				<span class="flex items-center px-3 text-sm">{{ meta.current_page }} / {{ meta.last_page }}</span>
				<Button variant="outline" size="sm" :disabled="meta.current_page >= meta.last_page" @click="goToPage('/finance/accounts', meta.current_page + 1, filters)">Próxima</Button>
			</div>
		</div>
	</AppLayout>
</template>
