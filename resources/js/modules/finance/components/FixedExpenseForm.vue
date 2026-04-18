<script setup lang="ts">
import { computed } from 'vue';

import { store, update } from '@/actions/App/Domain/FixedExpense/Controllers/FixedExpensePageController';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import ValidatedField from '@/components/ValidatedField.vue';
import ValidatedInertiaForm from '@/components/ValidatedInertiaForm.vue';

import type { Category } from '@/domain/Category/types/category';
import type { FixedExpense } from '@/domain/FixedExpense/types/fixed-expense';
import { fixedExpenseSchema } from '@/domain/FixedExpense/validations/fixed-expense-schema';

const props = defineProps<{
	item?: FixedExpense;
	readonly?: boolean;
	categories: Category[];
}>();

const emit = defineEmits<{
	success: [];
	cancel: [];
}>();

const isEditing = computed(() => !!props.item);
const action = computed(() =>
	isEditing.value ? update.url(props.item!.uid) : store.url()
);
const method = computed(() => (isEditing.value ? 'put' : 'post'));

const initialValues = computed(() => ({
	description: props.item?.description ?? '',
	amount: props.item?.amount ?? 0,
	due_day: props.item?.due_day ?? 1,
	category_uid: props.item?.category?.uid ?? '',
	active: props.item?.active ?? true,
}));
</script>

<template>
	<ValidatedInertiaForm
		:schema="fixedExpenseSchema"
		:initial-values="initialValues"
		:action="action"
		:method="method"
		@success="emit('success')"
	>
		<template #default="{ processing }">
			<div class="space-y-4">
				<ValidatedField name="description" label="Descrição">
					<template #default="{ field }">
						<Input
							v-bind="field"
							placeholder="Descrição da despesa"
							:disabled="props.readonly"
						/>
					</template>
				</ValidatedField>

				<div class="grid gap-4 md:grid-cols-3">
					<ValidatedField name="amount" label="Valor">
						<template #default="{ field }">
							<Input
								v-bind="field"
								type="number"
								step="0.01"
								min="0.01"
								:disabled="props.readonly"
							/>
						</template>
					</ValidatedField>

					<ValidatedField name="due_day" label="Dia Vencimento">
						<template #default="{ field }">
							<Input
								v-bind="field"
								type="number"
								min="1"
								max="31"
								:disabled="props.readonly"
							/>
						</template>
					</ValidatedField>

					<ValidatedField name="category_uid" label="Categoria">
						<template #default="{ field, handleChange }">
							<Select
								:model-value="field.value as string"
								:disabled="props.readonly"
								@update:model-value="handleChange"
							>
								<SelectTrigger>
									<SelectValue placeholder="Selecione" />
								</SelectTrigger>
								<SelectContent>
									<SelectItem v-for="cat in categories" :key="cat.uid" :value="cat.uid">
										{{ cat.name }}
									</SelectItem>
								</SelectContent>
							</Select>
						</template>
					</ValidatedField>
				</div>

				<ValidatedField name="active">
					<template #default="{ field, handleChange }">
						<div class="flex items-center gap-2">
							<Checkbox
								id="active"
								:model-value="!!field.value"
								:disabled="props.readonly"
								@update:model-value="handleChange"
							/>
							<Label for="active">Ativa</Label>
						</div>
					</template>
				</ValidatedField>

				<div v-if="!props.readonly" class="flex justify-end gap-2">
					<Button type="button" variant="outline" @click="emit('cancel')">
						Cancelar
					</Button>
					<Button type="submit" :disabled="processing">
						{{ isEditing ? 'Salvar' : 'Criar' }}
					</Button>
				</div>
			</div>
		</template>
	</ValidatedInertiaForm>
</template>
