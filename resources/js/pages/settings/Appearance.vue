<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import DashboardLayout from '@/layouts/DashboardLayout.vue';
import SettingsLayout from '@/layouts/SettingsLayout.vue';

defineOptions({ layout: DashboardLayout });
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
