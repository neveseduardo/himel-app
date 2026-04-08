<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppLayout from '@/components/layouts/AppLayout.vue';
import SettingsLayout from '@/components/layouts/settings/Layout.vue';

defineOptions({ layout: AppLayout });
</script>

<template>
	<div>
		<SettingsLayout>
			<Head title="Appearance settings" />

			<div class="space-y-6">
				<Heading
					variant="small"
					title="Appearance settings"
					description="Update your account's appearance settings"
				/>
				<AppearanceTabs />
			</div>
		</SettingsLayout>
	</div>
</template>
