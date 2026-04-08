<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { Account } from '../types/finance';

const props = defineProps<{
	account?: Account;
}>();

const isEditing = !!props.account;

const form = useForm({
	name: props.account?.name ?? '',
	type: props.account?.type ?? 'CHECKING',
	balance: props.account?.balance ?? 0,
});

function submit() {
	if (isEditing) {
		form.put(`/finance/accounts/${props.account!.uid}`);
	} else {
		form.post('/finance/accounts');
	}
}
</script>

<template>
	<form @submit.prevent="submit">
		<Card>
			<CardHeader>
				<CardTitle>{{ isEditing ? 'Editar Conta' : 'Nova Conta' }}</CardTitle>
			</CardHeader>
			<CardContent class="space-y-4">
				<div class="space-y-2">
					<Label for="name">Nome</Label>
					<Input id="name" v-model="form.name" placeholder="Nome da conta" />
					<p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
				</div>
				<div class="space-y-2">
					<Label for="type">Tipo</Label>
					<Select v-model="form.type">
						<SelectTrigger>
							<SelectValue placeholder="Selecione o tipo" />
						</SelectTrigger>
						<SelectContent>
							<SelectItem value="CHECKING">Conta Corrente</SelectItem>
							<SelectItem value="SAVINGS">Poupança</SelectItem>
							<SelectItem value="CASH">Dinheiro</SelectItem>
							<SelectItem value="OTHER">Outro</SelectItem>
						</SelectContent>
					</Select>
					<p v-if="form.errors.type" class="text-sm text-destructive">{{ form.errors.type }}</p>
				</div>
				<div class="space-y-2">
					<Label for="balance">Saldo Inicial</Label>
					<Input id="balance" v-model.number="form.balance" type="number" step="0.01" min="0" />
					<p v-if="form.errors.balance" class="text-sm text-destructive">{{ form.errors.balance }}</p>
				</div>
			</CardContent>
			<CardFooter class="flex justify-end gap-2">
				<Button type="button" variant="outline" @click="$inertia.visit('/finance/accounts')">Cancelar</Button>
				<Button type="submit" :disabled="form.processing">{{ isEditing ? 'Salvar' : 'Criar' }}</Button>
			</CardFooter>
		</Card>
	</form>
</template>
