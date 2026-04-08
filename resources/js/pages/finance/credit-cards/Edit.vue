<script setup lang="ts">
import AppLayout from '@/components/layouts/AppLayout.vue';
import CreditCardForm from '@/modules/finance/components/CreditCardForm.vue';
import type { CreditCard } from '@/modules/finance/types/finance';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
	creditCard: CreditCard;
}>();

const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Cartões', href: '/finance/credit-cards' },
	{ title: 'Editar', href: `/finance/credit-cards/${props.creditCard.uid}/edit` },
];
</script>

<template>
	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="mx-auto max-w-2xl p-6">
			<CreditCardForm :credit-card="creditCard" />
		</div>
	</AppLayout>
</template>
