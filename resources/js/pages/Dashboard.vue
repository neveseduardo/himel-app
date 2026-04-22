<template>
	<div>
		<Head title="Dashboard">
			<meta name="description" content="Painel principal da sua gestão financeira.">
		</Head>
	</div>
</template>

<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';

onMounted(() => {
	router.visit('/periods');
});
</script>
