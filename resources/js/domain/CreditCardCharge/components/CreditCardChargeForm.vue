<script setup lang="ts">
import { store } from '@/actions/App/Domain/CreditCardCharge/Controllers/CreditCardChargePageController';
import type { CreditCard } from '@/domain/CreditCard/types/credit-card';
import type { CreditCardCharge } from '@/domain/CreditCardCharge/types/credit-card-charge';
import { creditCardChargeSchema } from '@/domain/CreditCardCharge/validations/credit-card-charge-schema';

const props = defineProps<{
	item?: CreditCardCharge;
	readonly?: boolean;
	creditCards: CreditCard[];
}>();

const emit = defineEmits<{
	success: [];
	cancel: [];
}>();

const action = computed(() => store.url());
const method = computed(() => 'post' as const);

const initialValues = computed(() => ({
	credit_card_uid: props.item?.credit_card?.uid ?? '',
	description: props.item?.description ?? '',
	amount: props.item?.amount ?? 0,
	total_installments: props.item?.total_installments ?? 1,
}));
</script>

<template>
	<ValidatedInertiaForm
		:schema="creditCardChargeSchema"
		:initial-values="initialValues"
		:action="action"
		:method="method"
		@success="emit('success')"
	>
		<template #default="{ processing }">
			<div class="space-y-4">
				<ValidatedField name="credit_card_uid" label="Cartão">
					<template #default="{ field, handleChange }">
						<Select
							:model-value="field.value as string"
							:disabled="props.readonly"
							@update:model-value="handleChange"
						>
							<SelectTrigger>
								<SelectValue placeholder="Selecione o cartão" />
							</SelectTrigger>
							<SelectContent>
								<SelectItem v-for="card in creditCards" :key="card.uid" :value="card.uid">
									{{ card.name }} (•••• {{ card.last_four_digits }})
								</SelectItem>
							</SelectContent>
						</Select>
					</template>
				</ValidatedField>

				<ValidatedField name="description" label="Descrição">
					<template #default="{ field }">
						<Input
							v-bind="field"
							placeholder="Descrição da compra"
							:disabled="props.readonly"
						/>
					</template>
				</ValidatedField>

				<div class="grid gap-4 md:grid-cols-2">
					<ValidatedField name="amount" label="Valor Total">
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

					<ValidatedField name="total_installments" label="Parcelas">
						<template #default="{ field }">
							<Input
								v-bind="field"
								type="number"
								min="1"
								max="48"
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
						Criar
					</Button>
				</div>
			</div>
		</template>
	</ValidatedInertiaForm>
</template>
