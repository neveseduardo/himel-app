<script setup lang="ts">
import AppLayout from '@/components/layouts/AppLayout.vue';
import FixedExpenseForm from '@/modules/finance/components/FixedExpenseForm.vue';
import type { Category } from '@/modules/finance/types/finance';
import type { BreadcrumbItem } from '@/types';

defineProps<{
	categories: Category[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Despesas Fixas', href: '/finance/fixed-expenses' },
	{ title: 'Nova Despesa Fixa', href: '/finance/fixed-expenses/create' },
];
</script>

<template>
	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="mx-auto max-w-2xl p-6">
			<FixedExpenseForm :categories="categories" />
		</div>
	</AppLayout>
</template>
