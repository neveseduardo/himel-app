<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { VNode } from 'vue';

import type { BreadcrumbItem as BreadcrumbItemType } from '@/domain/Shared/types/navigation';

defineProps<{
	title: string;
	breadcrumbs?: BreadcrumbItemType[];
}>();

defineSlots<{
	back?: () => VNode[];
	actions?: () => VNode[];
}>();
</script>

<template>
	<div>
		<Breadcrumb v-if="breadcrumbs?.length">
			<BreadcrumbList>
				<template v-for="(item, index) in breadcrumbs" :key="index">
					<BreadcrumbItem>
						<BreadcrumbLink v-if="index < breadcrumbs.length - 1" as-child>
							<Link :href="item.href">
								{{ item.title }}
							</Link>
						</BreadcrumbLink>
						<BreadcrumbPage v-else>
							{{ item.title }}
						</BreadcrumbPage>
					</BreadcrumbItem>
					<BreadcrumbSeparator v-if="index < breadcrumbs.length - 1" />
				</template>
			</BreadcrumbList>
		</Breadcrumb>

		<div class="flex items-center justify-between">
			<div class="flex items-center gap-3">
				<slot name="back" />
				<h1 class="text-2xl font-semibold">
					{{ title }}
				</h1>
			</div>
			<div v-if="$slots.actions" class="flex items-center gap-2">
				<slot name="actions" />
			</div>
		</div>
	</div>
</template>
