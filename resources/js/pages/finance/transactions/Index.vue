<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Eye, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

import { destroy, index } from '@/actions/App/Domain/Transaction/Controllers/TransactionPageController';
import DeleteConfirmPopover from '@/components/DeleteConfirmPopover.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import ModalDialog from '@/components/ui/modal/ModalDialog.vue';
import type { Account } from '@/domain/Account/types/account';
import type { Category } from '@/domain/Category/types/category';
import DataTable from '@/domain/Shared/components/DataTable.vue';
import DirectionBadge from '@/domain/Shared/components/DirectionBadge.vue';
import FilterBar from '@/domain/Shared/components/FilterBar.vue';
import StatusBadge from '@/domain/Shared/components/StatusBadge.vue';
import { useCrudToast } from '@/domain/Shared/composables/useCrudToast';
import { useFinanceFilters } from '@/domain/Shared/composables/useFinanceFilters';
import { usePagination } from '@/domain/Shared/composables/usePagination';
import { formatCurrency, formatDate } from '@/domain/Shared/services/format';
import type { PaginationMeta } from '@/domain/Shared/types/pagination';
import TransactionForm from '@/domain/Transaction/components/TransactionForm.vue';
import { useTransactionStore } from '@/domain/Transaction/stores/useTransactionStore';
import type { Transaction } from '@/domain/Transaction/types/transaction';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
	transactions: Transaction[];
	meta: PaginationMeta;
	filters: Record<string, string>;
	accounts?: Account[];
	categories?: Category[];
}>();

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Transações', href: index.url() },
];

const columns = [
	{ key: 'description', label: 'Descrição' },
	{ key: 'amount', label: 'Valor' },
	{ key: 'direction', label: 'Direção' },
	{ key: 'status', label: 'Status' },
	{ key: 'occurred_at', label: 'Data' },
	{ key: 'actions', label: '' },
];

const store = useTransactionStore();
const { onSuccess, onError } = useCrudToast('Transação');
const { filters, applyFilters, resetFilters } = useFinanceFilters(props.filters);
const { goToPage } = usePagination();

const modalRef = ref<InstanceType<typeof ModalDialog> | null>(null);

watch(() => store.isModalOpen, (open) => {
	if (open) modalRef.value?.openDialog();
	else modalRef.value?.closeDialog();
});

const modalTitle = computed(() => {
	if (store.modalMode === 'create') return 'Nova Transação';
	if (store.modalMode === 'edit') return 'Editar Transação';
	return 'Detalhes da Transação';
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
		<PageHeader title="Transações" button-label="Criar" :button-icon="Plus" @action="store.openCreateModal()" />

		<FilterBar v-model="filters.search" @search="applyFilters(index.url())" @reset="resetFilters(index.url())" />

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
				<div class="flex justify-end gap-1">
					<Button variant="ghost" size="icon" @click="store.openViewModal(row as unknown as Transaction)">
						<Eye class="size-4" />
					</Button>
					<Button variant="ghost" size="icon" @click="store.openEditModal(row as unknown as Transaction)">
						<Pencil class="size-4" />
					</Button>
					<DeleteConfirmPopover :loading="store.deletingUid === (row as unknown as Transaction).uid" @confirm="handleDelete((row as unknown as Transaction).uid)">
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
			<TransactionForm
				:item="store.modalMode !== 'create' ? store.currentItem ?? undefined : undefined"
				:readonly="store.modalMode === 'view'"
				:accounts="accounts ?? []"
				:categories="categories ?? []"
				@success="handleFormSuccess"
				@cancel="store.closeModal()"
			/>
		</ModalDialog>
	</div>
</template>
