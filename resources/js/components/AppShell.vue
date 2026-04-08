<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';

defineProps<{
	variant?: 'header' | 'sidebar';
}>();
</script>

<template>
	<SidebarProvider v-if="variant === 'sidebar'">
		<slot />
	</SidebarProvider>
	<div v-else class="flex min-h-svh w-full flex-col">
		<slot />
	</div>
</template>
