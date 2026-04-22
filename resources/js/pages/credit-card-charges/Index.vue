<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Eye, Pencil, Plus, Trash2 } from 'lucide-vue-next';

import { destroy, index } from '@/actions/App/Domain/CreditCardCharge/Controllers/CreditCardChargePageController';
import type { CreditCard } from '@/domain/CreditCard/types/credit-card';
import CreditCardChargeForm from '@/domain/CreditCardCharge/components/CreditCardChargeForm.vue';
import { useCreditCardChargeStore } from '@/domain/CreditCardCharge/stores/useCreditCardChargeStore';
import type { CreditCardCharge } from '@/domain/CreditCardCharge/types/credit-card-charge';
import DataTable from '@/domain/Shared/components/DataTable.vue';
import FilterBar from '@/domain/Shared/components/FilterBar.vue';
import ModalDialog from '@/domain/Shared/components/ui/modal/ModalDialog.vue';
import { useCrudToast } from '@/domain/Shared/composables/useCrudToast';
import { useFinanceFilters } from '@/domain/Shared/composables/useFinanceFilters';
import { usePagination } from '@/domain/Shared/composables/usePagination';
import { formatCurrency } from '@/domain/Shared/services/format';
import type { PaginationMeta } from '@/domain/Shared/types/pagination';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
	charges: CreditCardCharge[];
	meta: PaginationMeta;
	filters: Record<string, string>;
	creditCards: CreditCard[];
}>();

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/' },
	{ title: 'Compras Cartão', href: index.url() },
];

const columns = [
	{ key: 'description', label: 'Descrição' },
	{ key: 'purchase_date', label: 'Data da Compra' },
	{ key: 'amount', label: 'Valor Total' },
	{ key: 'total_installments', label: 'Parcelas' },
	{ key: 'credit_card', label: 'Cartão' },
	{ key: 'actions', label: '' },
];

const store = useCreditCardChargeStore();
const { onSuccess, onError } = useCrudToast('Compra no cartão');
const { filters, applyFilters, resetFilters } = useFinanceFilters(props.filters);
const { goToPage } = usePagination();

const modalRef = ref<InstanceType<typeof ModalDialog> | null>(null);

watch(() => store.isModalOpen, (open) => {
	if (open) modalRef.value?.openDialog();
	else modalRef.value?.closeDialog();
});

const modalTitle = computed(() => {
	if (store.modalMode === 'create') return 'Nova Compra';
	if (store.modalMode === 'edit') return 'Editar Compra';
	return 'Detalhes da Compra';
});

function handleDialogOpenChange(open: boolean) {
	if (!open && store.isModalOpen) {
		store.closeModal();
	}
}

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
		<Head title="Compras no Cartão">
			<meta name="description" content="Acompanhe as compras realizadas nos seus cartões de crédito.">
		</Head>

		<PageHeader title="Compras no Cartão" button-label="Criar" :button-icon="Plus" @action="store.openCreateModal()" />

		<FilterBar v-model="filters.search" @search="applyFilters(index.url())" @reset="resetFilters(index.url())">
			<Select v-model="filters.card_uid" @update:model-value="applyFilters(index.url())">
				<SelectTrigger class="w-[180px] shrink-0">
					<SelectValue placeholder="Todos os cartões" />
				</SelectTrigger>
				<SelectContent>
					<SelectItem value="all">
						Todos os cartões
					</SelectItem>
					<SelectItem v-for="card in creditCards" :key="card.uid" :value="card.uid">
						{{ card.name }}
					</SelectItem>
				</SelectContent>
			</Select>
		</FilterBar>

		<DataTable :columns="columns" :data="charges as unknown as Record<string, unknown>[]">
			<template #cell-purchase_date="{ row }">
				{{ (row as unknown as CreditCardCharge).purchase_date
					? (row as unknown as CreditCardCharge).purchase_date.substring(0, 10).split('-').reverse().join('/')
					: '—' }}
			</template>
			<template #cell-amount="{ row }">
				{{ formatCurrency((row as unknown as CreditCardCharge).amount) }}
			</template>
			<template #cell-total_installments="{ row }">
				{{ (row as unknown as CreditCardCharge).total_installments }}x
			</template>
			<template #cell-credit_card="{ row }">
				{{ (row as unknown as CreditCardCharge).credit_card?.name ?? '—' }}
			</template>
			<template #cell-actions="{ row }">
				<div class="flex justify-end gap-1">
					<Button variant="ghost" size="icon" @click="store.openViewModal(row as unknown as CreditCardCharge)">
						<Eye class="size-4" />
					</Button>
					<Button variant="ghost" size="icon" @click="store.openEditModal(row as unknown as CreditCardCharge)">
						<Pencil class="size-4" />
					</Button>
					<DeleteConfirmPopover :loading="store.deletingUid === (row as unknown as CreditCardCharge).uid" @confirm="handleDelete((row as unknown as CreditCardCharge).uid)">
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

		<ModalDialog ref="modalRef" :title="modalTitle" @update:open="handleDialogOpenChange">
			<CreditCardChargeForm
				:item="store.modalMode !== 'create' ? store.currentItem ?? undefined : undefined"
				:readonly="store.modalMode === 'view'"
				:credit-cards="creditCards"
				@success="handleFormSuccess"
				@cancel="store.closeModal()"
			/>
		</ModalDialog>
	</div>
</template>
