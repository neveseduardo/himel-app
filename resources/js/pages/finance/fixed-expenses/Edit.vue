<script setup lang="ts">
import AppLayout from '@/components/layouts/AppLayout.vue';
import FixedExpenseForm from '@/modules/finance/components/FixedExpenseForm.vue';
import type { Category, FixedExpense } from '@/modules/finance/types/finance';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
	fixedExpense: FixedExpense;
	categories: Category[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Despesas Fixas', href: '/finance/fixed-expenses' },
	{ title: 'Editar', href: `/finance/fixed-expenses/${props.fixedExpense.uid}/edit` },
];
</script>

<template>
	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="mx-auto max-w-2xl p-6">
			<FixedExpenseForm :fixed-expense="fixedExpense" :categories="categories" />
		</div>
	</AppLayout>
</template>
