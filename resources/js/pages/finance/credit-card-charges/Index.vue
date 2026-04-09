<script setup lang="ts">
import { Eye, Plus } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

import { index } from '@/actions/App/Domain/CreditCardCharge/Controllers/CreditCardChargePageController';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import ModalDialog from '@/components/ui/modal/ModalDialog.vue';
import CreditCardChargeForm from '@/modules/finance/components/CreditCardChargeForm.vue';
import DataTable from '@/modules/finance/components/DataTable.vue';
import FilterBar from '@/modules/finance/components/FilterBar.vue';
import { useCrudToast } from '@/modules/finance/composables/useCrudToast';
import { useFinanceFilters } from '@/modules/finance/composables/useFinanceFilters';
import { usePagination } from '@/modules/finance/composables/usePagination';
import { formatCurrency } from '@/modules/finance/services/finance.services';
import { useCreditCardChargeStore } from '@/modules/finance/stores/useCreditCardChargeStore';
import type { CreditCard, CreditCardCharge, PaginationMeta } from '@/modules/finance/types/finance';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
	charges: CreditCardCharge[];
	meta: PaginationMeta;
	filters: Record<string, string>;
	creditCards?: CreditCard[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Compras Cartão', href: index.url() },
];

const columns = [
	{ key: 'description', label: 'Descrição' },
	{ key: 'total_amount', label: 'Valor Total' },
	{ key: 'installments', label: 'Parcelas' },
	{ key: 'credit_card', label: 'Cartão' },
	{ key: 'actions', label: '' },
];

const store = useCreditCardChargeStore();
const { onSuccess } = useCrudToast('Compra no cartão');
const { filters, applyFilters, resetFilters } = useFinanceFilters(props.filters);
const { goToPage } = usePagination();

const modalRef = ref<InstanceType<typeof ModalDialog> | null>(null);

watch(() => store.isModalOpen, (open) => {
	if (open) modalRef.value?.openDialog();
	else modalRef.value?.closeDialog();
});

const modalTitle = computed(() => {
	if (store.modalMode === 'create') return 'Nova Compra';
	return 'Detalhes da Compra';
});

function handleFormSuccess() {
	onSuccess('create');
	store.closeModal();
}
</script>

<template>
	<div class="flex flex-col gap-6 p-6">
		<PageHeader title="Compras no Cartão" button-label="Criar" :button-icon="Plus" @action="store.openCreateModal()" />

		<FilterBar v-model="filters.search" @search="applyFilters(index.url())" @reset="resetFilters(index.url())" />

		<DataTable :columns="columns" :data="charges as unknown as Record<string, unknown>[]">
			<template #cell-total_amount="{ row }">
				{{ formatCurrency((row as unknown as CreditCardCharge).total_amount) }}
			</template>
			<template #cell-installments="{ row }">
				{{ (row as unknown as CreditCardCharge).installments }}x
			</template>
			<template #cell-credit_card="{ row }">
				{{ (row as unknown as CreditCardCharge).credit_card?.name ?? '—' }}
			</template>
			<template #cell-actions="{ row }">
				<div class="flex justify-end gap-1">
					<Button variant="ghost" size="icon" @click="store.openViewModal(row as unknown as CreditCardCharge)">
						<Eye class="size-4" />
					</Button>
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
			<CreditCardChargeForm
				:item="store.modalMode !== 'create' ? store.currentItem ?? undefined : undefined"
				:readonly="store.modalMode === 'view'"
				:credit-cards="creditCards ?? []"
				@success="handleFormSuccess"
				@cancel="store.closeModal()"
			/>
		</ModalDialog>
	</div>
</template>
