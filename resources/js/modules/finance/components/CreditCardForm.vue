<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { CreditCard } from '../types/finance';

const props = defineProps<{
	creditCard?: CreditCard;
}>();

const isEditing = !!props.creditCard;

const form = useForm({
	name: props.creditCard?.name ?? '',
	closing_day: props.creditCard?.closing_day ?? 1,
	due_day: props.creditCard?.due_day ?? 10,
	card_type: props.creditCard?.card_type ?? 'PHYSICAL',
	last_four_digits: props.creditCard?.last_four_digits ?? '',
});

function submit() {
	if (isEditing) {
		form.put(`/finance/credit-cards/${props.creditCard!.uid}`);
	} else {
		form.post('/finance/credit-cards');
	}
}
</script>

<template>
	<form @submit.prevent="submit">
		<Card>
			<CardHeader>
				<CardTitle>{{ isEditing ? 'Editar Cartão' : 'Novo Cartão' }}</CardTitle>
			</CardHeader>
			<CardContent class="space-y-4">
				<div class="space-y-2">
					<Label for="name">Nome</Label>
					<Input id="name" v-model="form.name" placeholder="Nome do cartão" />
					<p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
				</div>
				<div class="grid gap-4 md:grid-cols-2">
					<div class="space-y-2">
						<Label for="closing_day">Dia Fechamento</Label>
						<Input id="closing_day" v-model.number="form.closing_day" type="number" min="1" max="31" />
						<p v-if="form.errors.closing_day" class="text-sm text-destructive">{{ form.errors.closing_day }}</p>
					</div>
					<div class="space-y-2">
						<Label for="due_day">Dia Vencimento</Label>
						<Input id="due_day" v-model.number="form.due_day" type="number" min="1" max="31" />
						<p v-if="form.errors.due_day" class="text-sm text-destructive">{{ form.errors.due_day }}</p>
					</div>
				</div>
				<div class="grid gap-4 md:grid-cols-2">
					<div class="space-y-2">
						<Label for="card_type">Tipo</Label>
						<Select v-model="form.card_type">
							<SelectTrigger>
								<SelectValue />
							</SelectTrigger>
							<SelectContent>
								<SelectItem value="PHYSICAL">Físico</SelectItem>
								<SelectItem value="VIRTUAL">Virtual</SelectItem>
							</SelectContent>
						</Select>
						<p v-if="form.errors.card_type" class="text-sm text-destructive">{{ form.errors.card_type }}</p>
					</div>
					<div class="space-y-2">
						<Label for="last_four_digits">Últimos 4 dígitos</Label>
						<Input id="last_four_digits" v-model="form.last_four_digits" maxlength="4" placeholder="0000" />
						<p v-if="form.errors.last_four_digits" class="text-sm text-destructive">{{ form.errors.last_four_digits }}</p>
					</div>
				</div>
			</CardContent>
			<CardFooter class="flex justify-end gap-2">
				<Button type="button" variant="outline" @click="$inertia.visit('/finance/credit-cards')">Cancelar</Button>
				<Button type="submit" :disabled="form.processing">{{ isEditing ? 'Salvar' : 'Criar' }}</Button>
			</CardFooter>
		</Card>
	</form>
</template>
