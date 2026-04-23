<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
	ArrowRightLeft,
	CalendarClock,
	CalendarDays,
	CreditCard,
	ShoppingCart,
	Tags,
	Wallet,
} from 'lucide-vue-next';

import { useCurrentUrl } from '@/domain/Shared/composables/useCurrentUrl';
import { toUrl } from '@/domain/Shared/lib/utils';
import type { NavItem } from '@/types';

const financeNavItems: NavItem[] = [
	{ title: 'Períodos', href: '/periods', icon: CalendarDays },
	{ title: 'Contas', href: '/accounts', icon: Wallet },
	{ title: 'Categorias', href: '/categories', icon: Tags },
	{ title: 'Transferências', href: '/transfers', icon: ArrowRightLeft },
	{ title: 'Despesas Fixas', href: '/fixed-expenses', icon: CalendarClock },
	{ title: 'Cartões', href: '/credit-cards', icon: CreditCard },
	{ title: 'Compras Cartão', href: '/credit-card-charges', icon: ShoppingCart },
];

const { isCurrentOrParentUrl } = useCurrentUrl();

function isActive(item: NavItem): boolean {
	return isCurrentOrParentUrl(item.href);
}
</script>

<template>
	<Sidebar collapsible="icon">
		<SidebarHeader>
			<SidebarMenu>
				<SidebarMenuItem>
					<SidebarMenuButton size="lg" as-child>
						<Link href="/">
							<div class="bg-primary text-primary-foreground flex aspect-square size-8 items-center justify-center rounded-lg">
								<Wallet class="size-4" />
							</div>
							<div class="grid flex-1 text-left text-sm leading-tight">
								<span class="truncate font-semibold">Himel</span>
								<span class="truncate text-xs">Gestão Financeira</span>
							</div>
						</Link>
					</SidebarMenuButton>
				</SidebarMenuItem>
			</SidebarMenu>
		</SidebarHeader>

		<SidebarContent>
			<SidebarGroup>
				<SidebarGroupLabel>Financeiro</SidebarGroupLabel>
				<SidebarGroupContent>
					<SidebarMenu>
						<SidebarMenuItem v-for="item in financeNavItems" :key="toUrl(item.href)">
							<SidebarMenuButton
								as-child
								:is-active="isActive(item)"
								:tooltip="item.title"
							>
								<Link :href="item.href">
									<component :is="item.icon" />
									<span>{{ item.title }}</span>
								</Link>
							</SidebarMenuButton>
						</SidebarMenuItem>
					</SidebarMenu>
				</SidebarGroupContent>
			</SidebarGroup>
		</SidebarContent>
	</Sidebar>
</template>
