<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle, Play, Plus, Trash2, X } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

import { detachTransactions, index, initialize, show } from '@/actions/App/Domain/Period/Controllers/PeriodPageController';
import { update as updateTransaction } from '@/actions/App/Domain/Transaction/Controllers/TransactionPageController';
import type { Account } from '@/domain/Account/types/account';
import type { Category } from '@/domain/Category/types/category';
import type { Period, PeriodCardBreakdown, PeriodFixedExpenses, PeriodInstallments, PeriodSummary } from '@/domain/Period/types/period';
import DataTable from '@/domain/Shared/components/DataTable.vue';
import DirectionBadge from '@/domain/Shared/components/DirectionBadge.vue';
import StatusBadge from '@/domain/Shared/components/StatusBadge.vue';
import ModalDialog from '@/domain/Shared/components/ui/modal/ModalDialog.vue';
import { useFinanceFilters } from '@/domain/Shared/composables/useFinanceFilters';
import { usePagination } from '@/domain/Shared/composables/usePagination';
import { formatCurrency, formatDate } from '@/domain/Shared/services/format';
import type { PaginationMeta } from '@/domain/Shared/types/pagination';
import TransactionForm from '@/domain/Transaction/components/TransactionForm.vue';
import type { Transaction } from '@/domain/Transaction/types/transaction';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
	period: Period;
	summary: PeriodSummary;
	transactions: Transaction[];
	meta: PaginationMeta;
	filters: Record<string, string>;
	accounts: Account[];
	categories: Category[];
	fixedExpenses: PeriodFixedExpenses;
	installments: PeriodInstallments;
	cardBreakdown: PeriodCardBreakdown;
}>();

const monthNames = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/' },
	{ title: 'Períodos', href: index.url() },
	{ title: `${monthNames[props.period.month]} ${props.period.year}`, href: show.url(props.period.uid) },
];

const columns = [
	{ key: 'description', label: 'Descrição' },
	{ key: 'category', label: 'Categoria' },
	{ key: 'account', label: 'Conta' },
	{ key: 'amount', label: 'Valor' },
	{ key: 'due_date', label: 'Vencimento' },
	{ key: 'status', label: 'Status' },
	{ key: 'actions', label: '' },
];

const { filters } = useFinanceFilters(props.filters);
const { goToPage } = usePagination();

const initializing = ref(false);
const payingUid = ref<string | null>(null);

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success as string | undefined);
const flashError = computed(() => page.props.flash?.error as string | undefined);

// 14.5 — Show flash notification after initialization
watch(flashSuccess, (msg) => {
	if (msg) toast.success(msg);
}, { immediate: true });

watch(flashError, (msg) => {
	if (msg) toast.error(msg);
}, { immediate: true });

// 14.1 — Initialize period action
function handleInitialize() {
	initializing.value = true;
	router.post(initialize.url(props.period.uid), {}, {
		onFinish: () => {
			initializing.value = false;
		},
	});
}

// 14.4 — Mark transaction as paid
function handleMarkAsPaid(transaction: Transaction) {
	payingUid.value = transaction.uid;
	router.put(updateTransaction.url(transaction.uid), {
		status: 'PAID',
		paid_at: new Date().toISOString().slice(0, 10),
	}, {
		preserveScroll: true,
		onSuccess: () => {
			payingUid.value = null;
			toast.success('Transação marcada como paga!');
		},
		onError: () => {
			payingUid.value = null;
			toast.error('Erro ao marcar transação como paga.');
		},
	});
}

// 14.3 — Apply filters
function applyFilters() {
	const cleanFilters = Object.fromEntries(
		Object.entries(filters.value).filter(([, v]) => v !== null && v !== '' && v !== 'all')
	);
	router.get(show.url(props.period.uid), cleanFilters, { preserveState: true, preserveScroll: true });
}

function resetFilters() {
	filters.value = {};
	router.get(show.url(props.period.uid), {}, { preserveState: true });
}

// 7.2 — Group transactions by direction
const inflowTransactions = computed(() =>
	props.transactions.filter((t) => t.direction === 'INFLOW')
);

const outflowTransactions = computed(() =>
	props.transactions.filter((t) => t.direction === 'OUTFLOW')
);

const inflowSubtotal = computed(() =>
	inflowTransactions.value.reduce((sum, t) => sum + Number(t.amount), 0)
);

const outflowSubtotal = computed(() =>
	outflowTransactions.value.reduce((sum, t) => sum + Number(t.amount), 0)
);

// 7.3 — Create transaction modal
const createModalRef = ref<InstanceType<typeof ModalDialog> | null>(null);

const periodDate = computed(() => {
	const month = String(props.period.month).padStart(2, '0');
	return `${props.period.year}-${month}-01`;
});

function handleCreateSuccess() {
	createModalRef.value?.closeDialog();
}

// 7.4 — Detach all transactions
const detaching = ref(false);

function handleDetachAll() {
	detaching.value = true;
	router.delete(detachTransactions.url(props.period.uid), {
		preserveScroll: true,
		onSuccess: () => {
			detaching.value = false;
		},
		onError: () => {
			detaching.value = false;
			toast.error('Erro ao remover transações do período.');
		},
	});
}
</script>

<template>
	<div class="flex flex-col gap-6 p-6">
		<Head :title="`${monthNames[period.month]} ${period.year}`">
			<meta name="description" :content="`Resumo financeiro do período ${monthNames[period.month]} ${period.year}.`">
		</Head>

		<!-- 14.1 — Header with month/year and actions -->
		<div class="flex items-center justify-between">
			<div class="flex items-center gap-3">
				<Button variant="ghost" size="icon" @click="router.get(index.url())">
					<ArrowLeft class="size-4" />
				</Button>
				<h1 class="text-2xl font-semibold">
					{{ monthNames[period.month] }} {{ period.year }}
				</h1>
			</div>
			<div class="flex items-center gap-2">
				<AlertDialog>
					<AlertDialogTrigger as-child>
						<Button variant="destructive" size="sm">
							<Trash2 class="mr-2 size-4" />
							Remover Todas as Transações
						</Button>
					</AlertDialogTrigger>
					<AlertDialogContent>
						<AlertDialogHeader>
							<AlertDialogTitle>Remover todas as transações?</AlertDialogTitle>
							<AlertDialogDescription>
								Essa ação irá desvincular todas as transações deste período. As transações não serão excluídas, apenas removidas do período.
							</AlertDialogDescription>
						</AlertDialogHeader>
						<AlertDialogFooter>
							<AlertDialogCancel>Cancelar</AlertDialogCancel>
							<AlertDialogAction :disabled="detaching" @click="handleDetachAll">
								{{ detaching ? 'Removendo...' : 'Confirmar Remoção' }}
							</AlertDialogAction>
						</AlertDialogFooter>
					</AlertDialogContent>
				</AlertDialog>
				<Button size="sm" @click="createModalRef?.openDialog()">
					<Plus class="mr-2 size-4" />
					Nova Transação
				</Button>
				<Button size="sm" :disabled="initializing" @click="handleInitialize">
					<Play class="mr-2 size-4" />
					{{ initializing ? 'Inicializando...' : 'Inicializar Período' }}
				</Button>
			</div>
		</div>

		<!-- 14.1 — Financial summary cards -->
		<div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
			<Card>
				<CardHeader class="pb-2">
					<CardTitle class="text-sm font-medium text-muted-foreground">
						Entradas
					</CardTitle>
				</CardHeader>
				<CardContent>
					<p class="text-2xl font-bold text-green-600 dark:text-green-400">
						{{ formatCurrency(summary.total_inflow) }}
					</p>
				</CardContent>
			</Card>
			<Card>
				<CardHeader class="pb-2">
					<CardTitle class="text-sm font-medium text-muted-foreground">
						Saídas
					</CardTitle>
				</CardHeader>
				<CardContent>
					<p class="text-2xl font-bold text-red-600 dark:text-red-400">
						{{ formatCurrency(summary.total_outflow) }}
					</p>
					<div class="mt-2 space-y-1 text-sm text-muted-foreground">
						<div class="flex justify-between">
							<span>Despesas Fixas</span>
							<span>{{ formatCurrency(summary.total_fixed_expenses ?? 0) }}</span>
						</div>
						<div class="flex justify-between">
							<span>Parcelas de Cartão</span>
							<span>{{ formatCurrency(summary.total_credit_card_installments ?? 0) }}</span>
						</div>
						<div class="flex justify-between">
							<span>Manuais</span>
							<span>{{ formatCurrency(summary.total_manual ?? 0) }}</span>
						</div>
						<div class="flex justify-between">
							<span>Transferências</span>
							<span>{{ formatCurrency(summary.total_transfer ?? 0) }}</span>
						</div>
					</div>
				</CardContent>
			</Card>
			<Card>
				<CardHeader class="pb-2">
					<CardTitle class="text-sm font-medium text-muted-foreground">
						Saldo
					</CardTitle>
				</CardHeader>
				<CardContent>
					<p class="text-2xl font-bold" :class="summary.balance >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
						{{ formatCurrency(summary.balance) }}
					</p>
				</CardContent>
			</Card>
		</div>

		<!-- Despesas Fixas section -->
		<Card>
			<CardHeader class="flex flex-row items-center justify-between pb-2">
				<CardTitle class="text-lg font-semibold">
					Despesas Fixas
				</CardTitle>
				<span class="text-lg font-bold text-muted-foreground">{{ formatCurrency(fixedExpenses.subtotal) }}</span>
			</CardHeader>
			<CardContent>
				<template v-if="fixedExpenses.items.length === 0">
					<p class="py-4 text-center text-sm text-muted-foreground">
						Nenhuma despesa fixa neste período.
					</p>
				</template>
				<template v-else>
					<Table>
						<TableHeader>
							<TableRow>
								<TableHead>Nome</TableHead>
								<TableHead>Valor</TableHead>
								<TableHead>Categoria</TableHead>
								<TableHead>Vencimento</TableHead>
							</TableRow>
						</TableHeader>
						<TableBody>
							<TableRow v-for="item in fixedExpenses.items" :key="item.transaction_uid">
								<TableCell>{{ item.description ?? '—' }}</TableCell>
								<TableCell>{{ formatCurrency(item.amount) }}</TableCell>
								<TableCell>{{ item.category_name ?? '—' }}</TableCell>
								<TableCell>{{ item.due_day ?? '—' }}</TableCell>
							</TableRow>
						</TableBody>
					</Table>
				</template>
			</CardContent>
		</Card>

		<!-- Parcelas de Cartão section -->
		<Card>
			<CardHeader class="flex flex-row items-center justify-between pb-2">
				<CardTitle class="text-lg font-semibold">
					Parcelas de Cartão
				</CardTitle>
				<span class="text-lg font-bold text-muted-foreground">{{ formatCurrency(installments.subtotal) }}</span>
			</CardHeader>
			<CardContent>
				<template v-if="installments.items.length === 0">
					<p class="py-4 text-center text-sm text-muted-foreground">
						Nenhuma parcela de cartão neste período.
					</p>
				</template>
				<template v-else>
					<Table>
						<TableHeader>
							<TableRow>
								<TableHead>Descrição</TableHead>
								<TableHead>Valor</TableHead>
								<TableHead>Vencimento</TableHead>
								<TableHead>Cartão</TableHead>
							</TableRow>
						</TableHeader>
						<TableBody>
							<TableRow v-for="item in installments.items" :key="item.transaction_uid">
								<TableCell>
									{{ item.charge_description ?? '—' }}
									<Badge v-if="item.installment_number != null && item.total_installments != null" :variant="item.installment_number === item.total_installments ? 'default' : 'secondary'" :class="[item.installment_number === item.total_installments ? 'bg-green-600 text-white hover:bg-green-600' : '', 'ml-2']">
										{{ item.installment_number }}/{{ item.total_installments }}
									</Badge>
								</TableCell>
								<TableCell>{{ formatCurrency(item.amount) }}</TableCell>
								<TableCell>{{ item.due_date ? formatDate(item.due_date) : '—' }}</TableCell>
								<TableCell>{{ item.credit_card_name ?? '—' }}</TableCell>
							</TableRow>
						</TableBody>
					</Table>
				</template>
			</CardContent>
		</Card>

		<!-- Resumo por Cartão section -->
		<Card v-if="cardBreakdown.cards.length > 0">
			<CardHeader class="pb-2">
				<CardTitle class="text-lg font-semibold">
					Resumo por Cartão
				</CardTitle>
			</CardHeader>
			<CardContent>
				<div class="space-y-2">
					<div v-for="card in cardBreakdown.cards" :key="card.credit_card_uid" class="flex items-center justify-between">
						<span class="text-sm">{{ card.credit_card_name }}</span>
						<span class="text-sm font-medium">{{ formatCurrency(card.total) }}</span>
					</div>
				</div>
			</CardContent>
			<CardFooter class="flex justify-between border-t pt-4">
				<span class="text-sm font-semibold">Total</span>
				<span class="text-sm font-bold">{{ formatCurrency(cardBreakdown.grand_total) }}</span>
			</CardFooter>
		</Card>

		<!-- 14.3 — Transaction filters -->
		<div class="flex flex-wrap items-end gap-3">
			<div class="min-w-[150px]">
				<label class="mb-1 block text-sm font-medium text-muted-foreground">Status</label>
				<Select v-model="filters.status">
					<SelectTrigger>
						<SelectValue placeholder="Todos" />
					</SelectTrigger>
					<SelectContent>
						<SelectItem value="all">
							Todos
						</SelectItem>
						<SelectItem value="PENDING">
							Pendente
						</SelectItem>
						<SelectItem value="PAID">
							Pago
						</SelectItem>
						<SelectItem value="OVERDUE">
							Atrasado
						</SelectItem>
					</SelectContent>
				</Select>
			</div>
			<div class="min-w-[150px]">
				<label class="mb-1 block text-sm font-medium text-muted-foreground">Direção</label>
				<Select v-model="filters.direction">
					<SelectTrigger>
						<SelectValue placeholder="Todas" />
					</SelectTrigger>
					<SelectContent>
						<SelectItem value="all">
							Todas
						</SelectItem>
						<SelectItem value="INFLOW">
							Entrada
						</SelectItem>
						<SelectItem value="OUTFLOW">
							Saída
						</SelectItem>
					</SelectContent>
				</Select>
			</div>
			<div class="min-w-[150px]">
				<label class="mb-1 block text-sm font-medium text-muted-foreground">Fonte</label>
				<Select v-model="filters.source">
					<SelectTrigger>
						<SelectValue placeholder="Todas" />
					</SelectTrigger>
					<SelectContent>
						<SelectItem value="all">
							Todas
						</SelectItem>
						<SelectItem value="MANUAL">
							Manual
						</SelectItem>
						<SelectItem value="CREDIT_CARD">
							Cartão
						</SelectItem>
						<SelectItem value="FIXED">
							Fixa
						</SelectItem>
						<SelectItem value="TRANSFER">
							Transferência
						</SelectItem>
					</SelectContent>
				</Select>
			</div>
			<div class="flex items-center gap-2">
				<Button variant="outline" size="sm" @click="applyFilters">
					Filtrar
				</Button>
				<Button variant="ghost" size="sm" @click="resetFilters">
					<X class="size-4" />
					Limpar
				</Button>
			</div>
		</div>

		<!-- 7.2 — Entradas (INFLOW) section -->
		<Card>
			<CardHeader class="flex flex-row items-center justify-between pb-2">
				<CardTitle class="text-lg font-semibold text-green-700 dark:text-green-400">
					Entradas
				</CardTitle>
				<span class="text-lg font-bold text-green-600 dark:text-green-400">{{ formatCurrency(inflowSubtotal) }}</span>
			</CardHeader>
			<CardContent>
				<template v-if="inflowTransactions.length === 0">
					<p class="py-4 text-center text-sm text-muted-foreground">
						Nenhuma entrada neste período.
					</p>
				</template>
				<template v-else>
					<DataTable :columns="columns" :data="inflowTransactions as unknown as Record<string, unknown>[]">
						<template #cell-description="{ row }">
							{{ (row as unknown as Transaction).description || '—' }}
						</template>
						<template #cell-category="{ row }">
							{{ (row as unknown as Transaction).category?.name || '—' }}
						</template>
						<template #cell-account="{ row }">
							{{ (row as unknown as Transaction).account?.name || '—' }}
						</template>
						<template #cell-amount="{ row }">
							<DirectionBadge :direction="(row as unknown as Transaction).direction" />
							{{ formatCurrency((row as unknown as Transaction).amount) }}
						</template>
						<template #cell-due_date="{ row }">
							{{ (row as unknown as Transaction).due_date ? formatDate((row as unknown as Transaction).due_date!) : '—' }}
						</template>
						<template #cell-status="{ row }">
							<StatusBadge :status="(row as unknown as Transaction).status" />
						</template>
						<template #cell-actions="{ row }">
							<div class="flex justify-end gap-1">
								<Button
									v-if="(row as unknown as Transaction).status !== 'PAID'"
									variant="ghost"
									size="icon"
									:disabled="payingUid === (row as unknown as Transaction).uid"
									title="Marcar como pago"
									@click="handleMarkAsPaid(row as unknown as Transaction)"
								>
									<CheckCircle class="size-4" />
								</Button>
							</div>
						</template>
					</DataTable>
				</template>
			</CardContent>
		</Card>

		<!-- 7.2 — Saídas (OUTFLOW) section -->
		<Card>
			<CardHeader class="flex flex-row items-center justify-between pb-2">
				<CardTitle class="text-lg font-semibold text-red-700 dark:text-red-400">
					Saídas
				</CardTitle>
				<span class="text-lg font-bold text-red-600 dark:text-red-400">{{ formatCurrency(outflowSubtotal) }}</span>
			</CardHeader>
			<CardContent>
				<template v-if="outflowTransactions.length === 0">
					<p class="py-4 text-center text-sm text-muted-foreground">
						Nenhuma saída neste período.
					</p>
				</template>
				<template v-else>
					<DataTable :columns="columns" :data="outflowTransactions as unknown as Record<string, unknown>[]">
						<template #cell-description="{ row }">
							{{ (row as unknown as Transaction).description || '—' }}
						</template>
						<template #cell-category="{ row }">
							{{ (row as unknown as Transaction).category?.name || '—' }}
						</template>
						<template #cell-account="{ row }">
							{{ (row as unknown as Transaction).account?.name || '—' }}
						</template>
						<template #cell-amount="{ row }">
							<DirectionBadge :direction="(row as unknown as Transaction).direction" />
							{{ formatCurrency((row as unknown as Transaction).amount) }}
						</template>
						<template #cell-due_date="{ row }">
							{{ (row as unknown as Transaction).due_date ? formatDate((row as unknown as Transaction).due_date!) : '—' }}
						</template>
						<template #cell-status="{ row }">
							<StatusBadge :status="(row as unknown as Transaction).status" />
						</template>
						<template #cell-actions="{ row }">
							<div class="flex justify-end gap-1">
								<Button
									v-if="(row as unknown as Transaction).status !== 'PAID'"
									variant="ghost"
									size="icon"
									:disabled="payingUid === (row as unknown as Transaction).uid"
									title="Marcar como pago"
									@click="handleMarkAsPaid(row as unknown as Transaction)"
								>
									<CheckCircle class="size-4" />
								</Button>
							</div>
						</template>
					</DataTable>
				</template>
			</CardContent>
		</Card>

		<!-- Pagination -->
		<div v-if="meta.last_page > 1" class="flex justify-center gap-2">
			<Button variant="outline" size="sm" :disabled="meta.current_page <= 1" @click="goToPage(show.url(period.uid), meta.current_page - 1, filters)">
				Anterior
			</Button>
			<span class="flex items-center px-3 text-sm">{{ meta.current_page }} / {{ meta.last_page }}</span>
			<Button variant="outline" size="sm" :disabled="meta.current_page >= meta.last_page" @click="goToPage(show.url(period.uid), meta.current_page + 1, filters)">
				Próxima
			</Button>
		</div>

		<!-- 7.3 — Create transaction modal -->
		<ModalDialog ref="createModalRef" title="Nova Transação" description="Criar transação vinculada ao período">
			<TransactionForm
				:accounts="accounts"
				:categories="categories"
				:period-uid="period.uid"
				:period-date="periodDate"
				@success="handleCreateSuccess"
				@cancel="createModalRef?.closeDialog()"
			/>
		</ModalDialog>
	</div>
</template>
