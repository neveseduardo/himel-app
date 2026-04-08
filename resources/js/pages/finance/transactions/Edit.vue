<script setup lang="ts">
import AppLayout from '@/components/layouts/AppLayout.vue';
import TransactionForm from '@/modules/finance/components/TransactionForm.vue';
import type { Account, Category, Transaction } from '@/modules/finance/types/finance';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
	transaction: Transaction;
	accounts: Account[];
	categories: Category[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Transações', href: '/finance/transactions' },
	{ title: 'Editar', href: `/finance/transactions/${props.transaction.uid}/edit` },
];
</script>

<template>
	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="mx-auto max-w-3xl p-6">
			<TransactionForm :transaction="transaction" :accounts="accounts" :categories="categories" />
		</div>
	</AppLayout>
</template>
