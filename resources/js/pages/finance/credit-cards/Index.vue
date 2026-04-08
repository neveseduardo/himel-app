<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Eye, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

import { destroy, index } from '@/actions/App/Domain/CreditCard/Controllers/CreditCardPageController';
import DeleteConfirmPopover from '@/components/DeleteConfirmPopover.vue';
import AppLayout from '@/components/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import ModalDialog from '@/components/ui/modal/ModalDialog.vue';
import CreditCardForm from '@/modules/finance/components/CreditCardForm.vue';
import DataTable from '@/modules/finance/components/DataTable.vue';
import FilterBar from '@/modules/finance/components/FilterBar.vue';
import { useCrudToast } from '@/modules/finance/composables/useCrudToast';
import { useFinanceFilters } from '@/modules/finance/composables/useFinanceFilters';
import { usePagination } from '@/modules/finance/composables/usePagination';
import { useCreditCardStore } from '@/modules/finance/stores/useCreditCardStore';
import type { CreditCard, PaginationMeta } from '@/modules/finance/types/finance';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
	creditCards: CreditCard[];
	meta: PaginationMeta;
	filters: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Cartões', href: index.url() },
];

const columns = [
	{ key: 'name', label: 'Nome' },
	{ key: 'last_four_digits', label: 'Final' },
	{ key: 'card_type', label: 'Tipo' },
	{ key: 'closing_day', label: 'Fechamento' },
	{ key: 'due_day', label: 'Vencimento' },
	{ key: 'actions', label: '' },
];

const store = useCreditCardStore();
const { onSuccess, onError } = useCrudToast('Cartão');
const { filters, applyFilters, resetFilters } = useFinanceFilters(props.filters);
const { goToPage } = usePagination();

const modalRef = ref<InstanceType<typeof ModalDialog> | null>(null);

watch(() => store.isModalOpen, (open) => {
	if (open) modalRef.value?.openDialog();
	else modalRef.value?.closeDialog();
});

const modalTitle = computed(() => {
	if (store.modalMode === 'create') return 'Novo Cartão';
	if (store.modalMode === 'edit') return 'Editar Cartão';
	return 'Detalhes do Cartão';
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
	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="flex flex-col gap-6 p-6">
			<PageHeader title="Cartões de Crédito" button-label="Criar" :button-icon="Plus" @action="store.openCreateModal()" />

			<FilterBar v-model="filters.search" @search="applyFilters(index.url())" @reset="resetFilters(index.url())" />

			<DataTable :columns="columns" :data="creditCards as unknown as Record<string, unknown>[]">
				<template #cell-last_four_digits="{ row }">
					•••• {{ (row as unknown as CreditCard).last_four_digits }}
				</template>
				<template #cell-card_type="{ row }">
					<Badge variant="outline">
						{{ (row as unknown as CreditCard).card_type === 'PHYSICAL' ? 'Físico' : 'Virtual' }}
					</Badge>
				</template>
				<template #cell-closing_day="{ value }">
					Dia {{ value }}
				</template>
				<template #cell-due_day="{ value }">
					Dia {{ value }}
				</template>
				<template #cell-actions="{ row }">
					<div class="flex justify-end gap-1">
						<Button variant="ghost" size="icon" @click="store.openViewModal(row as unknown as CreditCard)">
							<Eye class="size-4" />
						</Button>
						<Button variant="ghost" size="icon" @click="store.openEditModal(row as unknown as CreditCard)">
							<Pencil class="size-4" />
						</Button>
						<DeleteConfirmPopover :loading="store.deletingUid === (row as unknown as CreditCard).uid" @confirm="handleDelete((row as unknown as CreditCard).uid)">
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
				<CreditCardForm
					:item="store.modalMode !== 'create' ? store.currentItem ?? undefined : undefined"
					:readonly="store.modalMode === 'view'"
					@success="handleFormSuccess"
					@cancel="store.closeModal()"
				/>
			</ModalDialog>
		</div>
	</AppLayout>
</template>
