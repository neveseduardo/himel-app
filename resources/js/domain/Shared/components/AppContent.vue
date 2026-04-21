<script setup lang="ts">
defineProps<{
	variant?: 'header' | 'sidebar';
}>();
</script>

<template>
	<SidebarInset v-if="variant === 'sidebar'" v-bind="$attrs">
		<slot />
	</SidebarInset>
	<main v-else class="flex flex-1 flex-col" v-bind="$attrs">
		<slot />
	</main>
</template>
