<script setup lang="ts">
import AppLayout from '@/components/layouts/AppLayout.vue';
import CategoryForm from '@/modules/finance/components/CategoryForm.vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Categorias', href: '/finance/categories' },
	{ title: 'Nova Categoria', href: '/finance/categories/create' },
];
</script>

<template>
	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="mx-auto max-w-2xl p-6">
			<CategoryForm />
		</div>
	</AppLayout>
</template>
