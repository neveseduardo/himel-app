<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronsUpDown } from 'lucide-vue-next';

import {
	Breadcrumb,
	BreadcrumbItem,
	BreadcrumbLink,
	BreadcrumbList,
	BreadcrumbPage,
	BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import {
	DropdownMenu,
	DropdownMenuContent,
	DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Separator } from '@/components/ui/separator';
import { SidebarTrigger } from '@/components/ui/sidebar';
import UserMenuContent from '@/domain/Settings/components/UserMenuContent.vue';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

defineProps<{
	breadcrumbs?: BreadcrumbItemType[];
}>();

const page = usePage();
</script>

<template>
	<header class="flex h-16 shrink-0 items-center gap-2 border-b px-6">
		<div class="flex items-center gap-2">
			<SidebarTrigger class="-ml-1" />
			<Separator v-if="breadcrumbs?.length" orientation="vertical" class="mr-2 h-4" />
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
		</div>
		<div class="ml-auto flex items-center gap-2">
			<DropdownMenu>
				<DropdownMenuTrigger as-child>
					<button class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-left text-sm hover:bg-accent">
						<UserInfo :user="page.props.auth.user" :show-email="false" />
						<ChevronsUpDown class="ml-auto size-4" />
					</button>
				</DropdownMenuTrigger>
				<DropdownMenuContent class="w-56" align="end" side="bottom">
					<UserMenuContent :user="page.props.auth.user" />
				</DropdownMenuContent>
			</DropdownMenu>
		</div>
	</header>
</template>
