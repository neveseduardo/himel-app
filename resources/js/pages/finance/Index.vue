<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types';

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Financeiro', href: '/finance' },
];
</script>

<template>
	<div class="flex flex-col gap-6 p-6">
		<h1 class="text-2xl font-semibold">
			Visão Geral Financeira
		</h1>
		<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
			<Card>
				<CardHeader class="pb-2">
					<CardTitle class="text-sm font-medium text-muted-foreground">
						Saldo Total
					</CardTitle>
				</CardHeader>
				<CardContent>
					<p class="text-2xl font-bold">
						—
					</p>
				</CardContent>
			</Card>
			<Card>
				<CardHeader class="pb-2">
					<CardTitle class="text-sm font-medium text-muted-foreground">
						Entradas (Mês)
					</CardTitle>
				</CardHeader>
				<CardContent>
					<p class="text-2xl font-bold text-green-600">
						—
					</p>
				</CardContent>
			</Card>
			<Card>
				<CardHeader class="pb-2">
					<CardTitle class="text-sm font-medium text-muted-foreground">
						Saídas (Mês)
					</CardTitle>
				</CardHeader>
				<CardContent>
					<p class="text-2xl font-bold text-red-600">
						—
					</p>
				</CardContent>
			</Card>
			<Card>
				<CardHeader class="pb-2">
					<CardTitle class="text-sm font-medium text-muted-foreground">
						Pendentes
					</CardTitle>
				</CardHeader>
				<CardContent>
					<p class="text-2xl font-bold text-yellow-600">
						—
					</p>
				</CardContent>
			</Card>
		</div>
	</div>
</template>
