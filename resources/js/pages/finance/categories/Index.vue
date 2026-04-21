<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Eye, Pencil, Plus, Trash2 } from 'lucide-vue-next';

import { destroy, index } from '@/actions/App/Domain/Category/Controllers/CategoryPageController';
import CategoryForm from '@/domain/Category/components/CategoryForm.vue';
import { useCategoryStore } from '@/domain/Category/stores/useCategoryStore';
import type { Category } from '@/domain/Category/types/category';
import DataTable from '@/domain/Shared/components/DataTable.vue';
import DirectionBadge from '@/domain/Shared/components/DirectionBadge.vue';
import FilterBar from '@/domain/Shared/components/FilterBar.vue';
import ModalDialog from '@/domain/Shared/components/ui/modal/ModalDialog.vue';
import { useCrudToast } from '@/domain/Shared/composables/useCrudToast';
import { useFinanceFilters } from '@/domain/Shared/composables/useFinanceFilters';
import { usePagination } from '@/domain/Shared/composables/usePagination';
import type { PaginationMeta } from '@/domain/Shared/types/pagination';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
	categories: Category[];
	meta: PaginationMeta;
	filters: Record<string, string>;
}>();

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Categorias', href: index.url() },
];

const columns = [
	{ key: 'name', label: 'Nome' },
	{ key: 'direction', label: 'Direção' },
	{ key: 'actions', label: '' },
];

const store = useCategoryStore();
const { onSuccess, onError } = useCrudToast('Categoria');
const { filters, applyFilters, resetFilters } = useFinanceFilters(props.filters);
const { goToPage } = usePagination();

const modalRef = ref<InstanceType<typeof ModalDialog> | null>(null);

watch(() => store.isModalOpen, (open) => {
	if (open) modalRef.value?.openDialog();
	else modalRef.value?.closeDialog();
});

const modalTitle = computed(() => {
	if (store.modalMode === 'create') return 'Nova Categoria';
	if (store.modalMode === 'edit') return 'Editar Categoria';
	return 'Detalhes da Categoria';
});

function handleFormSuccess() {
	onSuccess(store.modalMode === 'edit' ? 'update' : 'create');
	store.closeModal();
}

function handleDelete(uid: string) {
	store.deletingUid = uid;
	router.delete(destroy.url(uid), {
		onSuccess: () => {
			store.deletingUid = null;
			onSuccess('delete');
		},
		onError: (errors) => {
			store.deletingUid = null;
			onError('delete', errors as Record<string, string>);
		},
	});
}
</script>

<template>
	<div class="flex flex-col gap-6 p-6">
		<PageHeader title="Categorias" button-label="Criar" :button-icon="Plus" @action="store.openCreateModal()" />

		<FilterBar v-model="filters.search" @search="applyFilters(index.url())" @reset="resetFilters(index.url())" />

		<DataTable :columns="columns" :data="categories as unknown as Record<string, unknown>[]">
			<template #cell-direction="{ row }">
				<DirectionBadge :direction="(row as unknown as Category).direction" />
			</template>
			<template #cell-actions="{ row }">
				<div class="flex justify-end gap-1">
					<Button variant="ghost" size="icon" @click="store.openViewModal(row as unknown as Category)">
						<Eye class="size-4" />
					</Button>
					<Button variant="ghost" size="icon" @click="store.openEditModal(row as unknown as Category)">
						<Pencil class="size-4" />
					</Button>
					<DeleteConfirmPopover :loading="store.deletingUid === (row as unknown as Category).uid" @confirm="handleDelete((row as unknown as Category).uid)">
						<template #trigger>
							<Button variant="ghost" size="icon">
								<Trash2 class="size-4" />
							</Button>
						</template>
					</DeleteConfirmPopover>
				</div>
			</template>
		</DataTable>

		<div v-if="meta.last_page > 1" class="flex justify-center gap-2">
			<Button variant="outline" size="sm" :disabled="meta.current_page <= 1" @click="goToPage(index.url(), meta.current_page - 1, filters)">
				Anterior
			</Button>
			<span class="flex items-center px-3 text-sm">{{ meta.current_page }} / {{ meta.last_page }}</span>
			<Button variant="outline" size="sm" :disabled="meta.current_page >= meta.last_page" @click="goToPage(index.url(), meta.current_page + 1, filters)">
				Próxima
			</Button>
		</div>

		<ModalDialog ref="modalRef" :title="modalTitle">
			<CategoryForm
				:item="store.modalMode !== 'create' ? store.currentItem ?? undefined : undefined"
				:readonly="store.modalMode === 'view'"
				@success="handleFormSuccess"
				@cancel="store.closeModal()"
			/>
		</ModalDialog>
	</div>
</template>
