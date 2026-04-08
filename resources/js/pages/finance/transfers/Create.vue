<script setup lang="ts">
import AppLayout from '@/components/layouts/AppLayout.vue';
import TransferForm from '@/modules/finance/components/TransferForm.vue';
import type { Account } from '@/modules/finance/types/finance';
import type { BreadcrumbItem } from '@/types';

defineProps<{
	accounts: Account[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Transferências', href: '/finance/transfers' },
	{ title: 'Nova Transferência', href: '/finance/transfers/create' },
];
</script>

<template>
	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="mx-auto max-w-2xl p-6">
			<TransferForm :accounts="accounts" />
		</div>
	</AppLayout>
</template>
