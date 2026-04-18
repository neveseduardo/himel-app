<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Eye, Pencil, Plus, Trash2 } from 'lucide-vue-next';

import { destroy, index } from '@/actions/App/Domain/Account/Controllers/AccountPageController';
import ModalDialog from '@/components/ui/modal/ModalDialog.vue';
import AccountForm from '@/domain/Account/components/AccountForm.vue';
import { useAccountStore } from '@/domain/Account/stores/useAccountStore';
import type { Account } from '@/domain/Account/types/account';
import DataTable from '@/domain/Shared/components/DataTable.vue';
import FilterBar from '@/domain/Shared/components/FilterBar.vue';
import { useCrudToast } from '@/domain/Shared/composables/useCrudToast';
import { useFinanceFilters } from '@/domain/Shared/composables/useFinanceFilters';
import { usePagination } from '@/domain/Shared/composables/usePagination';
import { formatCurrency } from '@/domain/Shared/services/format';
import type { PaginationMeta } from '@/domain/Shared/types/pagination';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
	accounts: Account[];
	meta: PaginationMeta;
	filters: Record<string, string>;
}>();

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Contas', href: index.url() },
];

const columns = [
	{ key: 'name', label: 'Nome' },
	{ key: 'type', label: 'Tipo' },
	{ key: 'balance', label: 'Saldo' },
	{ key: 'actions', label: '' },
];

const store = useAccountStore();
const { onSuccess, onError } = useCrudToast('Conta');
const { filters, applyFilters, resetFilters } = useFinanceFilters(props.filters);
const { goToPage } = usePagination();

const modalRef = ref<InstanceType<typeof ModalDialog> | null>(null);

watch(() => store.isModalOpen, (open) => {
	if (open) modalRef.value?.openDialog();
	else modalRef.value?.closeDialog();
});

const modalTitle = computed(() => {
	if (store.modalMode === 'create') return 'Nova Conta';
	if (store.modalMode === 'edit') return 'Editar Conta';
	return 'Detalhes da Conta';
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
		<PageHeader title="Contas" button-label="Criar" :button-icon="Plus" @action="store.openCreateModal()" />

		<FilterBar v-model="filters.search" @search="applyFilters(index.url())" @reset="resetFilters(index.url())" />

		<DataTable :columns="columns" :data="accounts as unknown as Record<string, unknown>[]">
			<template #cell-balance="{ row }">
				{{ formatCurrency((row as unknown as Account).balance) }}
			</template>
			<template #cell-actions="{ row }">
				<div class="flex justify-end gap-1">
					<Button variant="ghost" size="icon" @click="store.openViewModal(row as unknown as Account)">
						<Eye class="size-4" />
					</Button>
					<Button variant="ghost" size="icon" @click="store.openEditModal(row as unknown as Account)">
						<Pencil class="size-4" />
					</Button>
					<DeleteConfirmPopover :loading="store.deletingUid === (row as unknown as Account).uid" @confirm="handleDelete((row as unknown as Account).uid)">
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
			<AccountForm
				:item="store.modalMode !== 'create' ? store.currentItem ?? undefined : undefined"
				:readonly="store.modalMode === 'view'"
				@success="handleFormSuccess"
				@cancel="store.closeModal()"
			/>
		</ModalDialog>
	</div>
</template>
