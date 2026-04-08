<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { Account } from '../types/finance';

defineProps<{
	accounts: Account[];
}>();

const form = useForm({
	from_account_uid: '',
	to_account_uid: '',
	amount: 0,
	occurred_at: new Date().toISOString().substring(0, 10),
	description: '',
});

function submit() {
	form.post('/finance/transfers');
}
</script>

<template>
	<form @submit.prevent="submit">
		<Card>
			<CardHeader>
				<CardTitle>Nova Transferência</CardTitle>
			</CardHeader>
			<CardContent class="space-y-4">
				<div class="grid gap-4 md:grid-cols-2">
					<div class="space-y-2">
						<Label for="from_account_uid">Conta Origem</Label>
						<Select v-model="form.from_account_uid">
							<SelectTrigger>
								<SelectValue placeholder="Selecione a conta" />
							</SelectTrigger>
							<SelectContent>
								<SelectItem v-for="account in accounts" :key="account.uid" :value="account.uid">
									{{ account.name }}
								</SelectItem>
							</SelectContent>
						</Select>
						<p v-if="form.errors.from_account_uid" class="text-sm text-destructive">{{ form.errors.from_account_uid }}</p>
					</div>
					<div class="space-y-2">
						<Label for="to_account_uid">Conta Destino</Label>
						<Select v-model="form.to_account_uid">
							<SelectTrigger>
								<SelectValue placeholder="Selecione a conta" />
							</SelectTrigger>
							<SelectContent>
								<SelectItem v-for="account in accounts" :key="account.uid" :value="account.uid">
									{{ account.name }}
								</SelectItem>
							</SelectContent>
						</Select>
						<p v-if="form.errors.to_account_uid" class="text-sm text-destructive">{{ form.errors.to_account_uid }}</p>
					</div>
				</div>
				<div class="grid gap-4 md:grid-cols-2">
					<div class="space-y-2">
						<Label for="amount">Valor</Label>
						<Input id="amount" v-model.number="form.amount" type="number" step="0.01" min="0.01" />
						<p v-if="form.errors.amount" class="text-sm text-destructive">{{ form.errors.amount }}</p>
					</div>
					<div class="space-y-2">
						<Label for="occurred_at">Data</Label>
						<Input id="occurred_at" v-model="form.occurred_at" type="date" />
						<p v-if="form.errors.occurred_at" class="text-sm text-destructive">{{ form.errors.occurred_at }}</p>
					</div>
				</div>
				<div class="space-y-2">
					<Label for="description">Descrição</Label>
					<Input id="description" v-model="form.description" placeholder="Descrição (opcional)" />
					<p v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</p>
				</div>
			</CardContent>
			<CardFooter class="flex justify-end gap-2">
				<Button type="button" variant="outline" @click="$inertia.visit('/finance/transfers')">Cancelar</Button>
				<Button type="submit" :disabled="form.processing">Criar</Button>
			</CardFooter>
		</Card>
	</form>
</template>
