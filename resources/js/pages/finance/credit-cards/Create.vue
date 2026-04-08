<script setup lang="ts">
import AppLayout from '@/components/layouts/AppLayout.vue';
import CreditCardForm from '@/modules/finance/components/CreditCardForm.vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Cartões', href: '/finance/credit-cards' },
	{ title: 'Novo Cartão', href: '/finance/credit-cards/create' },
];
</script>

<template>
	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="mx-auto max-w-2xl p-6">
			<CreditCardForm />
		</div>
	</AppLayout>
</template>
