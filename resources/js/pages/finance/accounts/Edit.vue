<script setup lang="ts">
import AppLayout from '@/components/layouts/AppLayout.vue';
import AccountForm from '@/modules/finance/components/AccountForm.vue';
import type { Account } from '@/modules/finance/types/finance';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
	account: Account;
}>();

const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Contas', href: '/finance/accounts' },
	{ title: 'Editar', href: `/finance/accounts/${props.account.uid}/edit` },
];
</script>

<template>
	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="mx-auto max-w-2xl p-6">
			<AccountForm :account="account" />
		</div>
	</AppLayout>
</template>
