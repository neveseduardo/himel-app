<script setup lang="ts">
const { title = '', description = '' } = defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
	<AuthLayout :title="title" :description="description">
		<slot />
	</AuthLayout>
</template>
