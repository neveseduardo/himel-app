<script setup lang="ts">
import type { BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/app/AppSidebarLayout.vue';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();
</script>

<template>
	<AppLayout :breadcrumbs="breadcrumbs">
		<slot />
	</AppLayout>
</template>
