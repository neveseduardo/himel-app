<script setup lang="ts">
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import DataTable from '@/modules/finance/components/DataTable.vue';
import { useFinanceFilters } from '@/modules/finance/composables/useFinanceFilters';
import { usePagination } from '@/modules/finance/composables/usePagination';
import type { PaginationMeta, Period } from '@/modules/finance/types/finance';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
	periods: Period[];
	meta: PaginationMeta;
	filters: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Períodos', href: '/finance/periods' },
];

const columns = [
	{ key: 'month', label: 'Mês' },
	{ key: 'year', label: 'Ano' },
];

const monthNames = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];

const { filters } = useFinanceFilters(props.filters);
const { goToPage } = usePagination();
</script>

<template>
	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="flex flex-col gap-6 p-6">
			<h1 class="text-2xl font-semibold">
				Períodos
			</h1>

			<DataTable :columns="columns" :data="periods as unknown as Record<string, unknown>[]">
				<template #cell-month="{ row }">
					{{ monthNames[(row as unknown as Period).month] ?? (row as unknown as Period).month }}
				</template>
			</DataTable>

			<div v-if="meta.last_page > 1" class="flex justify-center gap-2">
				<Button variant="outline" size="sm" :disabled="meta.current_page <= 1" @click="goToPage('/finance/periods', meta.current_page - 1, filters)">
					Anterior
				</Button>
				<span class="flex items-center px-3 text-sm">{{ meta.current_page }} / {{ meta.last_page }}</span>
				<Button variant="outline" size="sm" :disabled="meta.current_page >= meta.last_page" @click="goToPage('/finance/periods', meta.current_page + 1, filters)">
					Próxima
				</Button>
			</div>
		</div>
	</AppLayout>
</template>
