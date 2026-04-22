<template>
	<div />
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';

onMounted(() => {
	router.visit('/periods');
});
</script>
