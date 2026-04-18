<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
	ArrowLeftRight,
	ArrowRightLeft,
	CalendarClock,
	CalendarDays,
	CreditCard,
	LayoutDashboard,
	ShoppingCart,
	Tags,
	Wallet,
} from 'lucide-vue-next';

import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import type { NavItem } from '@/types';

const financeNavItems: NavItem[] = [
	{ title: 'Visão Geral', href: '/finance', icon: LayoutDashboard },
	{ title: 'Períodos', href: '/finance/periods', icon: CalendarDays },
	{ title: 'Contas', href: '/finance/accounts', icon: Wallet },
	{ title: 'Categorias', href: '/finance/categories', icon: Tags },
	{ title: 'Transações', href: '/finance/transactions', icon: ArrowLeftRight },
	{ title: 'Transferências', href: '/finance/transfers', icon: ArrowRightLeft },
	{ title: 'Despesas Fixas', href: '/finance/fixed-expenses', icon: CalendarClock },
	{ title: 'Cartões', href: '/finance/credit-cards', icon: CreditCard },
	{ title: 'Compras Cartão', href: '/finance/credit-card-charges', icon: ShoppingCart },
];

const { isCurrentOrParentUrl, isCurrentUrl } = useCurrentUrl();

function isActive(item: NavItem): boolean {
	const href = toUrl(item.href);
	if (href === '/finance') {
		return isCurrentUrl(item.href);
	}
	return isCurrentOrParentUrl(item.href);
}
</script>

<template>
	<Sidebar collapsible="icon">
		<SidebarHeader>
			<SidebarMenu>
				<SidebarMenuItem>
					<SidebarMenuButton size="lg" as-child>
						<Link href="/finance">
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
