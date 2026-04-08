<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { CreditCard } from '../types/finance';

defineProps<{
	creditCards: CreditCard[];
}>();

const form = useForm({
	credit_card_uid: '',
	description: '',
	amount: 0,
	total_installments: 1,
});

function submit() {
	form.post('/finance/credit-card-charges');
}
</script>

<template>
	<form @submit.prevent="submit">
		<Card>
			<CardHeader>
				<CardTitle>Nova Compra no Cartão</CardTitle>
			</CardHeader>
			<CardContent class="space-y-4">
				<div class="space-y-2">
					<Label for="credit_card_uid">Cartão</Label>
					<Select v-model="form.credit_card_uid">
						<SelectTrigger>
							<SelectValue placeholder="Selecione o cartão" />
						</SelectTrigger>
						<SelectContent>
							<SelectItem v-for="card in creditCards" :key="card.uid" :value="card.uid">
								{{ card.name }} (•••• {{ card.last_four_digits }})
							</SelectItem>
						</SelectContent>
					</Select>
					<p v-if="form.errors.credit_card_uid" class="text-sm text-destructive">{{ form.errors.credit_card_uid }}</p>
				</div>
				<div class="space-y-2">
					<Label for="description">Descrição</Label>
					<Input id="description" v-model="form.description" placeholder="Descrição da compra" />
					<p v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</p>
				</div>
				<div class="grid gap-4 md:grid-cols-2">
					<div class="space-y-2">
						<Label for="amount">Valor Total</Label>
						<Input id="amount" v-model.number="form.amount" type="number" step="0.01" min="0.01" />
						<p v-if="form.errors.amount" class="text-sm text-destructive">{{ form.errors.amount }}</p>
					</div>
					<div class="space-y-2">
						<Label for="total_installments">Parcelas</Label>
						<Input id="total_installments" v-model.number="form.total_installments" type="number" min="1" max="48" />
						<p v-if="form.errors.total_installments" class="text-sm text-destructive">{{ form.errors.total_installments }}</p>
					</div>
				</div>
			</CardContent>
			<CardFooter class="flex justify-end gap-2">
				<Button type="button" variant="outline" @click="$inertia.visit('/finance/credit-card-charges')">Cancelar</Button>
				<Button type="submit" :disabled="form.processing">Criar</Button>
			</CardFooter>
		</Card>
	</form>
</template>
