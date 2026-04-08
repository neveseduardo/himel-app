<script setup lang="ts">
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { formatCurrency, formatDate } from '@/modules/finance/services/finance.services';
import type { CreditCardCharge, CreditCardInstallment } from '@/modules/finance/types/finance';
import type { BreadcrumbItem } from '@/types';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
	charge: CreditCardCharge & { installments?: CreditCardInstallment[] };
}>();

const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Compras Cartão', href: '/finance/credit-card-charges' },
	{ title: 'Detalhes', href: `/finance/credit-card-charges/${props.charge.uid}` },
];
</script>

<template>
	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="mx-auto max-w-3xl space-y-6 p-6">
			<div class="flex items-center justify-between">
				<h1 class="text-2xl font-semibold">Detalhes da Compra</h1>
				<Link href="/finance/credit-card-charges">
					<Button variant="outline">Voltar</Button>
				</Link>
			</div>

			<Card>
				<CardHeader>
					<CardTitle>{{ charge.description }}</CardTitle>
				</CardHeader>
				<CardContent class="space-y-2">
					<p>Cartão: {{ charge.credit_card?.name ?? '—' }}</p>
					<p>Valor Total: {{ formatCurrency(charge.total_amount) }}</p>
					<p>Parcelas: {{ charge.installments?.length ?? 0 }}x</p>
				</CardContent>
			</Card>

			<Card v-if="charge.installments?.length">
				<CardHeader>
					<CardTitle>Parcelas</CardTitle>
				</CardHeader>
				<CardContent>
					<Table>
						<TableHeader>
							<TableRow>
								<TableHead>#</TableHead>
								<TableHead>Valor</TableHead>
								<TableHead>Vencimento</TableHead>
							</TableRow>
						</TableHeader>
						<TableBody>
							<TableRow v-for="inst in charge.installments" :key="inst.uid">
								<TableCell>{{ inst.installment_number }}</TableCell>
								<TableCell>{{ formatCurrency(inst.amount) }}</TableCell>
								<TableCell>{{ formatDate(inst.due_date) }}</TableCell>
							</TableRow>
						</TableBody>
					</Table>
				</CardContent>
			</Card>
		</div>
	</AppLayout>
</template>
