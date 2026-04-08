<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';

import {
	Breadcrumb,
	BreadcrumbItem,
	BreadcrumbLink,
	BreadcrumbList,
	BreadcrumbPage,
	BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import UserMenuContent from '@/modules/settings/components/UserMenuContent.vue';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

defineProps<{
	breadcrumbs?: BreadcrumbItemType[];
}>();

const page = usePage();
</script>

<template>
	<header class="flex h-16 shrink-0 items-center gap-2 border-b px-6">
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
		<div class="ml-auto flex items-center gap-2">
			<UserMenuContent :user="page.props.auth.user" />
		</div>
	</header>
</template>
