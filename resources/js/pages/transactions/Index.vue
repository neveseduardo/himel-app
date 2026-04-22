<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ChevronDown, Eye, Pencil, Plus, Trash2 } from 'lucide-vue-next';

import { destroy, index } from '@/actions/App/Domain/Transaction/Controllers/TransactionPageController';
import type { Account } from '@/domain/Account/types/account';
import type { Category } from '@/domain/Category/types/category';
import DataTable from '@/domain/Shared/components/DataTable.vue';
import DirectionBadge from '@/domain/Shared/components/DirectionBadge.vue';
import FilterBar from '@/domain/Shared/components/FilterBar.vue';
import StatusBadge from '@/domain/Shared/components/StatusBadge.vue';
import ModalDialog from '@/domain/Shared/components/ui/modal/ModalDialog.vue';
import { useCrudToast } from '@/domain/Shared/composables/useCrudToast';
import { useFinanceFilters } from '@/domain/Shared/composables/useFinanceFilters';
import { usePagination } from '@/domain/Shared/composables/usePagination';
import { formatCurrency, formatDate } from '@/domain/Shared/services/format';
import type { PaginationMeta } from '@/domain/Shared/types/pagination';
import InflowTransactionForm from '@/domain/Transaction/components/InflowTransactionForm.vue';
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

const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/' },
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

const inflowModalRef = ref<InstanceType<typeof ModalDialog> | null>(null);
const outflowModalRef = ref<InstanceType<typeof ModalDialog> | null>(null);

watch(() => store.inflowModalOpen, (open) => {
	if (open) inflowModalRef.value?.openDialog();
	else inflowModalRef.value?.closeDialog();
});

watch(() => store.outflowModalOpen, (open) => {
	if (open) outflowModalRef.value?.openDialog();
	else outflowModalRef.value?.closeDialog();
});

const inflowModalTitle = computed(() => {
	if (store.modalMode === 'create') return 'Nova Entrada';
	if (store.modalMode === 'edit') return 'Editar Entrada';
	return 'Detalhes da Entrada';
});

const outflowModalTitle = computed(() => {
	if (store.modalMode === 'create') return 'Nova Saída';
	if (store.modalMode === 'edit') return 'Editar Saída';
	return 'Detalhes da Saída';
});

function handleFormSuccess() {
	onSuccess(store.modalMode === 'edit' ? 'update' : 'create');
	store.closeInflowModal();
	store.closeOutflowModal();
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
		<Head title="Transações">
			<meta name="description" content="Gerencie suas transações financeiras de entrada e saída.">
		</Head>

		<PageHeader title="Transações" :breadcrumbs="breadcrumbs">
			<template #actions>
				<DropdownMenu>
					<DropdownMenuTrigger as-child>
						<Button size="sm">
							<Plus class="size-4" />
							Nova Transação
							<ChevronDown class="ml-1 size-3" />
						</Button>
					</DropdownMenuTrigger>
					<DropdownMenuContent>
						<DropdownMenuItem @click="store.openCreateInflowModal()">
							Entrada
						</DropdownMenuItem>
						<DropdownMenuItem @click="store.openCreateOutflowModal()">
							Saída
						</DropdownMenuItem>
					</DropdownMenuContent>
				</DropdownMenu>
			</template>
		</PageHeader>

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

		<ModalDialog ref="inflowModalRef" :title="inflowModalTitle" @update:open="(open: boolean) => { if (!open) store.closeInflowModal(); }">
			<InflowTransactionForm
				:item="store.modalMode !== 'create' ? store.currentItem ?? undefined : undefined"
				:accounts="accounts ?? []"
				@success="handleFormSuccess"
				@cancel="store.closeInflowModal()"
			/>
		</ModalDialog>

		<ModalDialog ref="outflowModalRef" :title="outflowModalTitle" @update:open="(open: boolean) => { if (!open) store.closeOutflowModal(); }">
			<TransactionForm
				:item="store.modalMode !== 'create' ? store.currentItem ?? undefined : undefined"
				:readonly="store.modalMode === 'view'"
				:accounts="accounts ?? []"
				:categories="categories ?? []"
				@success="handleFormSuccess"
				@cancel="store.closeOutflowModal()"
			/>
		</ModalDialog>
	</div>
</template>
