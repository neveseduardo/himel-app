<script setup lang="ts">
import AppLayout from '@/components/layouts/AppLayout.vue';
import AccountForm from '@/modules/finance/components/AccountForm.vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
	{ title: 'Contas', href: '/finance/accounts' },
	{ title: 'Nova Conta', href: '/finance/accounts/create' },
];
</script>

<template>
	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="mx-auto max-w-2xl p-6">
			<AccountForm />
		</div>
	</AppLayout>
</template>
