<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { Account, Category, Transaction } from '../types/finance';

const props = defineProps<{
	transaction?: Transaction;
	accounts: Account[];
	categories: Category[];
}>();

const isEditing = !!props.transaction;

const form = useForm({
	account_uid: props.transaction?.account?.uid ?? '',
	category_uid: props.transaction?.category?.uid ?? '',
	amount: props.transaction?.amount ?? 0,
	direction: props.transaction?.direction ?? 'OUTFLOW',
	status: props.transaction?.status ?? 'PENDING',
	source: props.transaction?.source ?? 'MANUAL',
	description: props.transaction?.description ?? '',
	occurred_at: props.transaction?.occurred_at?.substring(0, 10) ?? new Date().toISOString().substring(0, 10),
	due_date: props.transaction?.due_date?.substring(0, 10) ?? '',
	paid_at: props.transaction?.paid_at?.substring(0, 10) ?? '',
});

function submit() {
	if (isEditing) {
		form.put(`/finance/transactions/${props.transaction!.uid}`);
	} else {
		form.post('/finance/transactions');
	}
}
</script>

<template>
	<form @submit.prevent="submit">
		<Card>
			<CardHeader>
				<CardTitle>{{ isEditing ? 'Editar Transação' : 'Nova Transação' }}</CardTitle>
			</CardHeader>
			<CardContent class="space-y-4">
				<div class="grid gap-4 md:grid-cols-2">
					<div class="space-y-2">
						<Label for="account_uid">Conta</Label>
						<Select v-model="form.account_uid">
							<SelectTrigger>
								<SelectValue placeholder="Selecione a conta" />
							</SelectTrigger>
							<SelectContent>
								<SelectItem v-for="account in accounts" :key="account.uid" :value="account.uid">
									{{ account.name }}
								</SelectItem>
							</SelectContent>
						</Select>
						<p v-if="form.errors.account_uid" class="text-sm text-destructive">{{ form.errors.account_uid }}</p>
					</div>
					<div class="space-y-2">
						<Label for="category_uid">Categoria</Label>
						<Select v-model="form.category_uid">
							<SelectTrigger>
								<SelectValue placeholder="Selecione a categoria" />
							</SelectTrigger>
							<SelectContent>
								<SelectItem v-for="category in categories" :key="category.uid" :value="category.uid">
									{{ category.name }}
								</SelectItem>
							</SelectContent>
						</Select>
						<p v-if="form.errors.category_uid" class="text-sm text-destructive">{{ form.errors.category_uid }}</p>
					</div>
				</div>
				<div class="grid gap-4 md:grid-cols-3">
					<div class="space-y-2">
						<Label for="amount">Valor</Label>
						<Input id="amount" v-model.number="form.amount" type="number" step="0.01" min="0.01" />
						<p v-if="form.errors.amount" class="text-sm text-destructive">{{ form.errors.amount }}</p>
					</div>
					<div class="space-y-2">
						<Label for="direction">Direção</Label>
						<Select v-model="form.direction">
							<SelectTrigger>
								<SelectValue />
							</SelectTrigger>
							<SelectContent>
								<SelectItem value="INFLOW">Entrada</SelectItem>
								<SelectItem value="OUTFLOW">Saída</SelectItem>
							</SelectContent>
						</Select>
						<p v-if="form.errors.direction" class="text-sm text-destructive">{{ form.errors.direction }}</p>
					</div>
					<div class="space-y-2">
						<Label for="status">Status</Label>
						<Select v-model="form.status">
							<SelectTrigger>
								<SelectValue />
							</SelectTrigger>
							<SelectContent>
								<SelectItem value="PENDING">Pendente</SelectItem>
								<SelectItem value="PAID">Pago</SelectItem>
							</SelectContent>
						</Select>
						<p v-if="form.errors.status" class="text-sm text-destructive">{{ form.errors.status }}</p>
					</div>
				</div>
				<div class="space-y-2">
					<Label for="description">Descrição</Label>
					<Input id="description" v-model="form.description" placeholder="Descrição (opcional)" />
					<p v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</p>
				</div>
				<div class="grid gap-4 md:grid-cols-3">
					<div class="space-y-2">
						<Label for="occurred_at">Data</Label>
						<Input id="occurred_at" v-model="form.occurred_at" type="date" />
						<p v-if="form.errors.occurred_at" class="text-sm text-destructive">{{ form.errors.occurred_at }}</p>
					</div>
					<div class="space-y-2">
						<Label for="due_date">Vencimento</Label>
						<Input id="due_date" v-model="form.due_date" type="date" />
						<p v-if="form.errors.due_date" class="text-sm text-destructive">{{ form.errors.due_date }}</p>
					</div>
					<div class="space-y-2">
						<Label for="paid_at">Data Pagamento</Label>
						<Input id="paid_at" v-model="form.paid_at" type="date" />
						<p v-if="form.errors.paid_at" class="text-sm text-destructive">{{ form.errors.paid_at }}</p>
					</div>
				</div>
			</CardContent>
			<CardFooter class="flex justify-end gap-2">
				<Button type="button" variant="outline" @click="$inertia.visit('/finance/transactions')">Cancelar</Button>
				<Button type="submit" :disabled="form.processing">{{ isEditing ? 'Salvar' : 'Criar' }}</Button>
			</CardFooter>
		</Card>
	</form>
</template>
