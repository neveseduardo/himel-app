<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { Category, FixedExpense } from '../types/finance';

const props = defineProps<{
	fixedExpense?: FixedExpense;
	categories: Category[];
}>();

const isEditing = !!props.fixedExpense;

const form = useForm({
	name: props.fixedExpense?.description ?? '',
	amount: props.fixedExpense?.amount ?? 0,
	due_day: props.fixedExpense?.due_day ?? 1,
	category_uid: props.fixedExpense?.category?.uid ?? '',
	active: props.fixedExpense?.active ?? true,
});

function submit() {
	if (isEditing) {
		form.put(`/finance/fixed-expenses/${props.fixedExpense!.uid}`);
	} else {
		form.post('/finance/fixed-expenses');
	}
}
</script>

<template>
	<form @submit.prevent="submit">
		<Card>
			<CardHeader>
				<CardTitle>{{ isEditing ? 'Editar Despesa Fixa' : 'Nova Despesa Fixa' }}</CardTitle>
			</CardHeader>
			<CardContent class="space-y-4">
				<div class="space-y-2">
					<Label for="name">Descrição</Label>
					<Input id="name" v-model="form.name" placeholder="Descrição da despesa" />
					<p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
				</div>
				<div class="grid gap-4 md:grid-cols-3">
					<div class="space-y-2">
						<Label for="amount">Valor</Label>
						<Input id="amount" v-model.number="form.amount" type="number" step="0.01" min="0.01" />
						<p v-if="form.errors.amount" class="text-sm text-destructive">{{ form.errors.amount }}</p>
					</div>
					<div class="space-y-2">
						<Label for="due_day">Dia Vencimento</Label>
						<Input id="due_day" v-model.number="form.due_day" type="number" min="1" max="31" />
						<p v-if="form.errors.due_day" class="text-sm text-destructive">{{ form.errors.due_day }}</p>
					</div>
					<div class="space-y-2">
						<Label for="category_uid">Categoria</Label>
						<Select v-model="form.category_uid">
							<SelectTrigger>
								<SelectValue placeholder="Selecione" />
							</SelectTrigger>
							<SelectContent>
								<SelectItem v-for="cat in categories" :key="cat.uid" :value="cat.uid">
									{{ cat.name }}
								</SelectItem>
							</SelectContent>
						</Select>
						<p v-if="form.errors.category_uid" class="text-sm text-destructive">{{ form.errors.category_uid }}</p>
					</div>
				</div>
				<div class="flex items-center gap-2">
					<Checkbox id="active" :model-value="form.active" @update:model-value="form.active = !!$event" />
					<Label for="active">Ativa</Label>
				</div>
			</CardContent>
			<CardFooter class="flex justify-end gap-2">
				<Button type="button" variant="outline" @click="$inertia.visit('/finance/fixed-expenses')">Cancelar</Button>
				<Button type="submit" :disabled="form.processing">{{ isEditing ? 'Salvar' : 'Criar' }}</Button>
			</CardFooter>
		</Card>
	</form>
</template>
