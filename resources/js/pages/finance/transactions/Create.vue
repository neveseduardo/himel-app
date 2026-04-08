<script setup lang="ts">
import AppLayout from '@/components/layouts/AppLayout.vue';
import TransactionForm from '@/modules/finance/components/TransactionForm.vue';
import type { Account, Category } from '@/modules/finance/types/finance';
import type { BreadcrumbItem } from '@/types';

defineProps<{
	accounts: Account[];
	categories: Category[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Transações', href: '/finance/transactions' },
	{ title: 'Nova Transação', href: '/finance/transactions/create' },
];
</script>

<template>
	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="mx-auto max-w-3xl p-6">
			<TransactionForm :accounts="accounts" :categories="categories" />
		</div>
	</AppLayout>
</template>
