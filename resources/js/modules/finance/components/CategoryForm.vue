<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { Category } from '../types/finance';

const props = defineProps<{
	category?: Category;
}>();

const isEditing = !!props.category;

const form = useForm({
	name: props.category?.name ?? '',
	direction: props.category?.direction ?? 'OUTFLOW',
});

function submit() {
	if (isEditing) {
		form.put(`/finance/categories/${props.category!.uid}`);
	} else {
		form.post('/finance/categories');
	}
}
</script>

<template>
	<form @submit.prevent="submit">
		<Card>
			<CardHeader>
				<CardTitle>{{ isEditing ? 'Editar Categoria' : 'Nova Categoria' }}</CardTitle>
			</CardHeader>
			<CardContent class="space-y-4">
				<div class="space-y-2">
					<Label for="name">Nome</Label>
					<Input id="name" v-model="form.name" placeholder="Nome da categoria" />
					<p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
				</div>
				<div class="space-y-2">
					<Label for="direction">Direção</Label>
					<Select v-model="form.direction">
						<SelectTrigger>
							<SelectValue placeholder="Selecione a direção" />
						</SelectTrigger>
						<SelectContent>
							<SelectItem value="INFLOW">Entrada</SelectItem>
							<SelectItem value="OUTFLOW">Saída</SelectItem>
						</SelectContent>
					</Select>
					<p v-if="form.errors.direction" class="text-sm text-destructive">{{ form.errors.direction }}</p>
				</div>
			</CardContent>
			<CardFooter class="flex justify-end gap-2">
				<Button type="button" variant="outline" @click="$inertia.visit('/finance/categories')">Cancelar</Button>
				<Button type="submit" :disabled="form.processing">{{ isEditing ? 'Salvar' : 'Criar' }}</Button>
			</CardFooter>
		</Card>
	</form>
</template>
