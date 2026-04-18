<script setup lang="ts">
import { computed } from 'vue';

import { store, update } from '@/actions/App/Domain/CreditCard/Controllers/CreditCardPageController';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import ValidatedField from '@/components/ValidatedField.vue';
import ValidatedInertiaForm from '@/components/ValidatedInertiaForm.vue';

import type { CreditCard } from '@/domain/CreditCard/types/credit-card';
import { creditCardSchema } from '@/domain/CreditCard/validations/credit-card-schema';

const props = defineProps<{
	item?: CreditCard;
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
	closing_day: props.item?.closing_day ?? 1,
	due_day: props.item?.due_day ?? 10,
	card_type: props.item?.card_type ?? 'PHYSICAL',
	last_four_digits: props.item?.last_four_digits ?? '',
}));
</script>

<template>
	<ValidatedInertiaForm
		:schema="creditCardSchema"
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
							placeholder="Nome do cartão"
							:disabled="props.readonly"
						/>
					</template>
				</ValidatedField>

				<div class="grid gap-4 md:grid-cols-2">
					<ValidatedField name="closing_day" label="Dia Fechamento">
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
				</div>

				<div class="grid gap-4 md:grid-cols-2">
					<ValidatedField name="card_type" label="Tipo">
						<template #default="{ field, handleChange }">
							<Select
								:model-value="field.value as string"
								:disabled="props.readonly"
								@update:model-value="handleChange"
							>
								<SelectTrigger>
									<SelectValue />
								</SelectTrigger>
								<SelectContent>
									<SelectItem value="PHYSICAL">
										Físico
									</SelectItem>
									<SelectItem value="VIRTUAL">
										Virtual
									</SelectItem>
								</SelectContent>
							</Select>
						</template>
					</ValidatedField>

					<ValidatedField name="last_four_digits" label="Últimos 4 dígitos">
						<template #default="{ field }">
							<Input
								v-bind="field"
								maxlength="4"
								placeholder="0000"
								:disabled="props.readonly"
							/>
						</template>
					</ValidatedField>
				</div>

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
