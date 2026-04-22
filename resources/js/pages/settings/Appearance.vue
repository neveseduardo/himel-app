<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
</script>

<template>
	<div>
		<Head title="Appearance settings">
			<meta name="description" content="Personalize a aparência da aplicação.">
		</Head>

		<div class="space-y-6">
			<Heading
				variant="small"
				title="Appearance settings"
				description="Update your account's appearance settings"
			/>
			<AppearanceTabs />
		</div>
	</div>
</template>
