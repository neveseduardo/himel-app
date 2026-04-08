<script setup lang="ts">
import AppLayout from '@/components/layouts/AppLayout.vue';
import CreditCardChargeForm from '@/modules/finance/components/CreditCardChargeForm.vue';
import type { CreditCard } from '@/modules/finance/types/finance';
import type { BreadcrumbItem } from '@/types';

defineProps<{
	creditCards: CreditCard[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Compras Cartão', href: '/finance/credit-card-charges' },
	{ title: 'Nova Compra', href: '/finance/credit-card-charges/create' },
];
</script>

<template>
	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="mx-auto max-w-2xl p-6">
			<CreditCardChargeForm :credit-cards="creditCards" />
		</div>
	</AppLayout>
</template>
