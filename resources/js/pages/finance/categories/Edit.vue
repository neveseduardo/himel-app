<script setup lang="ts">
import AppLayout from '@/components/layouts/AppLayout.vue';
import CategoryForm from '@/modules/finance/components/CategoryForm.vue';
import type { Category } from '@/modules/finance/types/finance';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
	category: Category;
}>();

const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Categorias', href: '/finance/categories' },
	{ title: 'Editar', href: `/finance/categories/${props.category.uid}/edit` },
];
</script>

<template>
	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="mx-auto max-w-2xl p-6">
			<CategoryForm :category="category" />
		</div>
	</AppLayout>
</template>
