<script setup lang="ts">
import { computed } from 'vue';

import { store, update } from '@/actions/App/Domain/Category/Controllers/CategoryPageController';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import ValidatedField from '@/components/ValidatedField.vue';
import ValidatedInertiaForm from '@/components/ValidatedInertiaForm.vue';

import type { Category } from '@/domain/Category/types/category';
import { categorySchema } from '../validations/category-schema';

const props = defineProps<{
	item?: Category;
	readonly?: boolean;
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
	name: props.item?.name ?? '',
	direction: props.item?.direction ?? 'OUTFLOW',
}));
</script>

<template>
	<ValidatedInertiaForm
		:schema="categorySchema"
		:initial-values="initialValues"
		:action="action"
		:method="method"
		@success="emit('success')"
	>
		<template #default="{ processing }">
			<div class="space-y-4">
				<ValidatedField name="name" label="Nome">
					<template #default="{ field }">
						<Input
							v-bind="field"
							placeholder="Nome da categoria"
							:disabled="props.readonly"
						/>
					</template>
				</ValidatedField>

				<ValidatedField name="direction" label="Direção">
					<template #default="{ field, handleChange }">
						<Select
							:model-value="field.value as string"
							:disabled="props.readonly"
							@update:model-value="handleChange"
						>
							<SelectTrigger>
								<SelectValue placeholder="Selecione a direção" />
							</SelectTrigger>
							<SelectContent>
								<SelectItem value="INFLOW">
									Entrada
								</SelectItem>
								<SelectItem value="OUTFLOW">
									Saída
								</SelectItem>
							</SelectContent>
						</Select>
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
