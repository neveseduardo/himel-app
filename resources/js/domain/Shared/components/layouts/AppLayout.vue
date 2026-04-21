<script setup lang="ts">
import type { BreadcrumbItem } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();
</script>

<template>
	<AppSidebarLayout :breadcrumbs="breadcrumbs">
		<slot />
	</AppSidebarLayout>
</template>
