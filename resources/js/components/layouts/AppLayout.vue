<script setup lang="ts">
import AppLayout from '@/components/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();
</script>

<template>
	<AppLayout :breadcrumbs="breadcrumbs">
		<slot />
	</AppLayout>
</template>
