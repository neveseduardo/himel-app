<script setup lang="ts">
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import DataTable from '@/modules/finance/components/DataTable.vue';
import DirectionBadge from '@/modules/finance/components/DirectionBadge.vue';
import FilterBar from '@/modules/finance/components/FilterBar.vue';
import { useFinanceFilters } from '@/modules/finance/composables/useFinanceFilters';
import { usePagination } from '@/modules/finance/composables/usePagination';
import type { Category, PaginationMeta } from '@/modules/finance/types/finance';
import type { BreadcrumbItem } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';

const props = defineProps<{
	categories: Category[];
	meta: PaginationMeta;
	filters: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Categorias', href: '/finance/categories' },
];

const columns = [
	{ key: 'name', label: 'Nome' },
	{ key: 'direction', label: 'Direção' },
	{ key: 'actions', label: '' },
];

const { filters, applyFilters, resetFilters } = useFinanceFilters(props.filters);
const { goToPage } = usePagination();

function deleteCategory(uid: string) {
	if (confirm('Tem certeza que deseja excluir esta categoria?')) {
		router.delete(`/finance/categories/${uid}`);
	}
}
</script>

<template>
	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="flex flex-col gap-6 p-6">
			<div class="flex items-center justify-between">
				<h1 class="text-2xl font-semibold">Categorias</h1>
				<Link href="/finance/categories/create">
					<Button size="sm"><Plus class="mr-2 size-4" /> Nova Categoria</Button>
				</Link>
			</div>

			<FilterBar v-model="filters.search" @search="applyFilters('/finance/categories')" @reset="resetFilters('/finance/categories')">
			</FilterBar>

			<DataTable :columns="columns" :data="categories as unknown as Record<string, unknown>[]">
				<template #cell-direction="{ row }">
					<DirectionBadge :direction="(row as unknown as Category).direction" />
				</template>
				<template #cell-actions="{ row }">
					<div class="flex justify-end gap-2">
						<Link :href="`/finance/categories/${(row as unknown as Category).uid}/edit`">
							<Button variant="outline" size="sm">Editar</Button>
						</Link>
						<Button variant="destructive" size="sm" @click="deleteCategory((row as unknown as Category).uid)">Excluir</Button>
					</div>
				</template>
			</DataTable>

			<div v-if="meta.last_page > 1" class="flex justify-center gap-2">
				<Button variant="outline" size="sm" :disabled="meta.current_page <= 1" @click="goToPage('/finance/categories', meta.current_page - 1, filters)">Anterior</Button>
				<span class="flex items-center px-3 text-sm">{{ meta.current_page }} / {{ meta.last_page }}</span>
				<Button variant="outline" size="sm" :disabled="meta.current_page >= meta.last_page" @click="goToPage('/finance/categories', meta.current_page + 1, filters)">Próxima</Button>
			</div>
		</div>
	</AppLayout>
</template>
