<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Eye, Plus, Trash2 } from 'lucide-vue-next';

import { destroy, index, show, store } from '@/actions/App/Domain/Period/Controllers/PeriodPageController';
import ModalDialog from '@/components/ui/modal/ModalDialog.vue';
import type { Period } from '@/domain/Period/types/period';
import DataTable from '@/domain/Shared/components/DataTable.vue';
import { useCrudToast } from '@/domain/Shared/composables/useCrudToast';
import { useFinanceFilters } from '@/domain/Shared/composables/useFinanceFilters';
import { usePagination } from '@/domain/Shared/composables/usePagination';
import type { PaginationMeta } from '@/domain/Shared/types/pagination';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
	periods: Period[];
	meta: PaginationMeta;
	filters: Record<string, string>;
}>();

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Períodos', href: index.url() },
];

const columns = [
	{ key: 'month', label: 'Mês' },
	{ key: 'year', label: 'Ano' },
	{ key: 'transactions_count', label: 'Transações' },
	{ key: 'actions', label: '' },
];

const monthNames = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];

const { onSuccess, onError } = useCrudToast('Período');
const { filters } = useFinanceFilters(props.filters);
const { goToPage } = usePagination();

const modalRef = ref<InstanceType<typeof ModalDialog> | null>(null);
const deletingUid = ref<string | null>(null);
const creating = ref(false);

const currentYear = new Date().getFullYear();
const selectedMonth = ref<string>(String(new Date().getMonth() + 1));
const selectedYear = ref<string>(String(currentYear));

const years = Array.from({ length: 5 }, (_, i) => currentYear - 2 + i);

function openCreateModal() {
	selectedMonth.value = String(new Date().getMonth() + 1);
	selectedYear.value = String(currentYear);
	modalRef.value?.openDialog();
}

function handleCreate() {
	creating.value = true;
	router.post(store.url(), {
		month: Number(selectedMonth.value),
		year: Number(selectedYear.value),
	}, {
		onSuccess: () => {
			creating.value = false;
			modalRef.value?.closeDialog();
			onSuccess('create');
		},
		onError: (errors) => {
			creating.value = false;
			onError('create', errors as Record<string, string>);
		},
	});
}

function handleNavigateToShow(period: Period) {
	router.get(show.url(period.uid));
}

function handleDelete(uid: string) {
	deletingUid.value = uid;
	router.delete(destroy.url(uid), {
		onSuccess: () => {
			deletingUid.value = null;
			onSuccess('delete');
		},
		onError: (errors) => {
			deletingUid.value = null;
			onError('delete', errors as Record<string, string>);
		},
	});
}
</script>

<template>
	<div class="flex flex-col gap-6 p-6">
		<PageHeader title="Períodos" button-label="Criar Período" :button-icon="Plus" @action="openCreateModal()" />

		<DataTable :columns="columns" :data="periods as unknown as Record<string, unknown>[]">
			<template #cell-month="{ row }">
				{{ monthNames[(row as unknown as Period).month] ?? (row as unknown as Period).month }}
			</template>
			<template #cell-transactions_count="{ row }">
				{{ (row as unknown as Period).transactions_count ?? 0 }}
			</template>
			<template #cell-actions="{ row }">
				<div class="flex justify-end gap-1">
					<Button variant="ghost" size="icon" @click="handleNavigateToShow(row as unknown as Period)">
						<Eye class="size-4" />
					</Button>
					<DeleteConfirmPopover :loading="deletingUid === (row as unknown as Period).uid" @confirm="handleDelete((row as unknown as Period).uid)">
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

		<ModalDialog ref="modalRef" title="Criar Período" description="Selecione o mês e ano para o novo período.">
			<div class="space-y-4">
				<div class="space-y-2">
					<Label for="month">Mês</Label>
					<Select v-model="selectedMonth">
						<SelectTrigger>
							<SelectValue placeholder="Selecione o mês" />
						</SelectTrigger>
						<SelectContent>
							<SelectItem v-for="m in 12" :key="m" :value="String(m)">
								{{ monthNames[m] }}
							</SelectItem>
						</SelectContent>
					</Select>
				</div>
				<div class="space-y-2">
					<Label for="year">Ano</Label>
					<Select v-model="selectedYear">
						<SelectTrigger>
							<SelectValue placeholder="Selecione o ano" />
						</SelectTrigger>
						<SelectContent>
							<SelectItem v-for="y in years" :key="y" :value="String(y)">
								{{ y }}
							</SelectItem>
						</SelectContent>
					</Select>
				</div>
				<div class="flex justify-end gap-2">
					<Button variant="outline" @click="modalRef?.closeDialog()">
						Cancelar
					</Button>
					<Button :disabled="creating" @click="handleCreate">
						{{ creating ? 'Criando...' : 'Criar' }}
					</Button>
				</div>
			</div>
		</ModalDialog>
	</div>
</template>
