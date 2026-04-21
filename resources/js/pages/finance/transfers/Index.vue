<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Eye, Plus, Trash2 } from 'lucide-vue-next';

import { destroy, index } from '@/actions/App/Domain/Transfer/Controllers/TransferPageController';
import type { Account } from '@/domain/Account/types/account';
import DataTable from '@/domain/Shared/components/DataTable.vue';
import FilterBar from '@/domain/Shared/components/FilterBar.vue';
import ModalDialog from '@/domain/Shared/components/ui/modal/ModalDialog.vue';
import { useCrudToast } from '@/domain/Shared/composables/useCrudToast';
import { useFinanceFilters } from '@/domain/Shared/composables/useFinanceFilters';
import { usePagination } from '@/domain/Shared/composables/usePagination';
import { formatCurrency, formatDate } from '@/domain/Shared/services/format';
import type { PaginationMeta } from '@/domain/Shared/types/pagination';
import TransferForm from '@/domain/Transfer/components/TransferForm.vue';
import { useTransferStore } from '@/domain/Transfer/stores/useTransferStore';
import type { Transfer } from '@/domain/Transfer/types/transfer';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
	transfers: Transfer[];
	meta: PaginationMeta;
	filters: Record<string, string>;
	accounts?: Account[];
}>();

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Transferências', href: index.url() },
];

const columns = [
	{ key: 'from_account', label: 'Origem' },
	{ key: 'to_account', label: 'Destino' },
	{ key: 'amount', label: 'Valor' },
	{ key: 'occurred_at', label: 'Data' },
	{ key: 'actions', label: '' },
];

const store = useTransferStore();
const { onSuccess, onError } = useCrudToast('Transferência');
const { filters, applyFilters, resetFilters } = useFinanceFilters(props.filters);
const { goToPage } = usePagination();

const modalRef = ref<InstanceType<typeof ModalDialog> | null>(null);

watch(() => store.isModalOpen, (open) => {
	if (open) modalRef.value?.openDialog();
	else modalRef.value?.closeDialog();
});

const modalTitle = computed(() => {
	if (store.modalMode === 'create') return 'Nova Transferência';
	return 'Detalhes da Transferência';
});

function handleFormSuccess() {
	onSuccess('create');
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
		<PageHeader title="Transferências" button-label="Criar" :button-icon="Plus" @action="store.openCreateModal()" />

		<FilterBar v-model="filters.search" @search="applyFilters(index.url())" @reset="resetFilters(index.url())" />

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
					<Button variant="ghost" size="icon" @click="store.openViewModal(row as unknown as Transfer)">
						<Eye class="size-4" />
					</Button>
					<DeleteConfirmPopover :loading="store.deletingUid === (row as unknown as Transfer).uid" @confirm="handleDelete((row as unknown as Transfer).uid)">
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
			<TransferForm
				:item="store.modalMode !== 'create' ? store.currentItem ?? undefined : undefined"
				:readonly="store.modalMode === 'view'"
				:accounts="accounts ?? []"
				@success="handleFormSuccess"
				@cancel="store.closeModal()"
			/>
		</ModalDialog>
	</div>
</template>
